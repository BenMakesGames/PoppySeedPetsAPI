<?php
namespace App\Controller\Item\PetAlteration;

use App\Controller\Item\PoppySeedPetsItemController;
use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetSkillEnum;
use App\Repository\MeritRepository;
use App\Repository\PetRepository;
use App\Service\InventoryService;
use App\Service\MeritService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/forgettingScroll")
 */
class ForgettingScrollController extends PoppySeedPetsItemController
{
    /**
     * @Route("/{inventory}/forgettableThings", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getForgettableThings(
        Inventory $inventory, ResponseService $responseService, Request $request, PetRepository $petRepository,
        MeritService $meritService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'forgettingScroll');

        $petId = $request->query->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('There is no such pet.');

        if($pet->getLevel() < 10)
            throw new UnprocessableEntityHttpException('Only pets of level 10 or greater may use this scroll.');

        $unlearnableSkills = array_values(array_filter(PetSkillEnum::getValues(), function(string $skill) use($pet) {
            return $pet->getSkills()->getStat($skill) > 0;
        }));

        $data = [
            'merits' => $meritService->getUnlearnableMerits($pet),
            'skills' => $unlearnableSkills,
        ];

        return $responseService->success($data);
    }

    /**
     * @Route("/{inventory}/forgetMerit", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function forgetMerit(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository, MeritRepository $meritRepository, MeritService $meritService,
        InventoryService $inventoryService
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'forgettingScroll');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('There is no such pet.');

        if($pet->getLevel() < 10)
            throw new UnprocessableEntityHttpException('Only pets of level 10 or greater may use this scroll.');

        $meritName = $request->request->get('merit', '');
        $merit = $meritRepository->findOneByName($meritName);

        if(!$merit)
            throw new UnprocessableEntityHttpException('You forgot to select a merit!');

        if(!$pet->hasMerit($merit->getName()))
            throw new UnprocessableEntityHttpException($pet->getName() . ' doesn\'t have that Merit.');

        if(!in_array($merit->getName(), $meritService->getUnlearnableMerits($pet)))
        {
            if($merit->getName() === MeritEnum::VOLAGAMY)
                throw new UnprocessableEntityHttpException('That merit cannot be unlearned while ' . $pet->getName() . ' ' . ($pet->getSpecies()->getEggImage() ? 'has an egg' : 'is pregnant') . '.');
            else
                throw new UnprocessableEntityHttpException('That merit cannot be unlearned.');
        }

        $em->remove($inventory);

        $pet->removeMerit($merit);

        $responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% has forgotten the "' . $merit->getName() . '" Merit!', 'items/scroll/unlearning');

        if(in_array($merit->getName(), MeritService::AFFECTION_MERITS))
            $pet->decreaseAffectionRewardsClaimed();

        if($merit->getName() === MeritEnum::BEHATTED)
        {
            if($pet->getHat())
            {
                $inventoryService->unhatPet($pet);

                $responseService->addFlashMessage($pet->getName() . '\'s hat falls to the ground.');
            }
        }
        else if($merit->getName() === MeritEnum::SPIRIT_COMPANION)
        {
            if($pet->getSpiritCompanion())
            {
                $responseService->addFlashMessage($pet->getSpiritCompanion()->getName() . ' fades away...');

                $em->remove($pet->getSpiritCompanion());
                $pet->setSpiritCompanion(null);
            }
        }

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }

    /**
     * @Route("/{inventory}/forgetSkill", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function forgetSkill(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em, Request $request,
        PetRepository $petRepository
    )
    {
        $user = $this->getUser();

        $this->validateInventory($inventory, 'forgettingScroll');

        $petId = $request->request->getInt('pet', 0);
        $pet = $petRepository->find($petId);

        if(!$pet || $pet->getOwner()->getId() !== $user->getId())
            throw new NotFoundHttpException('There is no such pet.');

        if($pet->getLevel() < 10)
            throw new UnprocessableEntityHttpException('Only pets of level 10 or greater may use this scroll.');

        $skill = $request->request->get('skill', '');

        if(!PetSkillEnum::isAValue($skill))
            throw new UnprocessableEntityHttpException('You gotta\' select a skill to forget!');

        if($pet->getSkills()->getStat($skill) < 1)
            throw new UnprocessableEntityHttpException($pet->getName() . ' does not have any points of ' . $skill . ' to unlearn.');

        $em->remove($inventory);

        $pet->getSkills()->decreaseStat($skill);

        $responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% has forgotten some details about ' . ucfirst($skill) . '!', 'items/scroll/unlearning');

        $em->flush();

        return $responseService->itemActionSuccess(null, [ 'itemDeleted' => true ]);
    }
}
