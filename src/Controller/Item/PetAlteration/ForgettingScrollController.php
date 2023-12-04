<?php
namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\MeritEnum;
use App\Enum\PetSkillEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\EquipmentFunctions;
use App\Functions\MeritFunctions;
use App\Functions\MeritRepository;
use App\Functions\PetActivityLogFactory;
use App\Model\MeritInfo;
use App\Repository\PetRepository;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @Route("/item/forgettingScroll")
 */
class ForgettingScrollController extends AbstractController
{
    #[Route("/{inventory}/forgettableThings", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getForgettableThings(
        Inventory $inventory, ResponseService $responseService, Request $request, PetRepository $petRepository
    )
    {
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'forgettingScroll');

        $petId = $request->query->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if($pet->getLevel() < 10)
            throw new PSPInvalidOperationException('Only pets of level 10 or greater may use this scroll.');

        $unlearnableSkills = array_values(array_filter(PetSkillEnum::getValues(), fn(string $skill) =>
            $pet->getSkills()->getStat($skill) > 0
        ));

        $data = [
            'merits' => MeritFunctions::getUnlearnableMerits($pet),
            'skills' => $unlearnableSkills,
        ];

        return $responseService->success($data);
    }

    #[Route("/{inventory}/forgetMerit", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function forgetMerit(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        UserStatsService $userStatsRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'forgettingScroll');

        $petId = $request->request->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if($pet->getLevel() < 10)
            throw new PSPInvalidOperationException('Only pets of level 10 or greater may use this scroll.');

        $meritName = $request->request->get('merit', '');
        $merit = MeritRepository::findOneByName($em, $meritName);

        if(!$pet->hasMerit($merit->getName()))
            throw new PSPNotFoundException($pet->getName() . ' doesn\'t have that Merit.');

        if(!in_array($merit->getName(), MeritFunctions::getUnlearnableMerits($pet)))
        {
            if($merit->getName() === MeritEnum::VOLAGAMY)
                throw new PSPInvalidOperationException('That merit cannot be unlearned while ' . $pet->getName() . ' ' . ($pet->getSpecies()->getEggImage() ? 'has an egg' : 'is pregnant') . '.');
            else
                throw new PSPInvalidOperationException('That merit cannot be unlearned.');
        }

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $em->remove($inventory);

        $pet->removeMerit($merit);

        PetActivityLogFactory::createUnreadLog($em, $pet, '%pet:' . $pet->getId() . '.name% has forgotten the "' . $merit->getName() . '" Merit!')
            ->setIcon('items/scroll/unlearning');

        if(in_array($merit->getName(), MeritInfo::AFFECTION_REWARDS))
            $pet->decreaseAffectionRewardsClaimed();

        if($merit->getName() === MeritEnum::BEHATTED)
        {
            if($pet->getHat())
            {
                EquipmentFunctions::unhatPet($pet);

                $responseService->addFlashMessage($pet->getName() . '\'s hat falls to the ground.');
            }
        }
        else if($merit->getName() === MeritEnum::SPIRIT_COMPANION)
        {
            if($pet->getSpiritCompanion())
            {
                $responseService->addFlashMessage($pet->getSpiritCompanion()->getName() . ' fades away...');

                if($pet->getSpiritCompanion()->getFatheredPets()->count() == 0)
                    $em->remove($pet->getSpiritCompanion());

                $pet->setSpiritCompanion(null);
            }
        }

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/forgetSkill", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function forgetSkill(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        UserStatsService $userStatsRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'forgettingScroll');

        $petId = $request->request->getInt('pet', 0);
        $pet = $em->getRepository(Pet::class)->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if($pet->getLevel() < 10)
            throw new PSPInvalidOperationException('Only pets of level 10 or greater may use this scroll.');

        $skill = $request->request->get('skill', '');

        if(!PetSkillEnum::isAValue($skill))
            throw new PSPFormValidationException('You gotta\' select a skill to forget!');

        if($pet->getSkills()->getStat($skill) < 1)
            throw new PSPInvalidOperationException($pet->getName() . ' does not have any points of ' . $skill . ' to unlearn.');

        $userStatsRepository->incrementStat($user, UserStatEnum::READ_A_SCROLL);

        $em->remove($inventory);

        $pet->getSkills()->decreaseStat($skill);

        PetActivityLogFactory::createUnreadLog($em, $pet, '%pet:' . $pet->getId() . '.name% has forgotten some details about ' . ucfirst($skill) . '!')
            ->setIcon('items/scroll/unlearning');

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
