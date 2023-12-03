<?php
namespace App\Controller\Pet;

use App\Entity\Merit;
use App\Entity\Pet;
use App\Entity\SpiritCompanion;
use App\Entity\User;
use App\Enum\MeritEnum;
use App\Enum\PetSkillEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Exceptions\PSPPetNotFoundException;
use App\Functions\ArrayFunctions;
use App\Functions\MeritFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\UserUnlockedFeatureHelpers;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route("/pet")]
class AffectionRewardController extends AbstractController
{
    /**
     * @Route("/{pet}/availableMerits", methods={"GET"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getAvailableMerits(Pet $pet, ResponseService $responseService, EntityManagerInterface $em)
    {
        /** @var User $user */
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        $merits = $em->getRepository(Merit::class)->findBy([ 'name' => MeritFunctions::getAvailableMerits($pet) ]);

        return $responseService->success($merits, [ SerializationGroupEnum::AVAILABLE_MERITS ]);
    }

    /**
     * @Route("/{pet}/chooseAffectionReward/merit", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function chooseAffectionRewardMerit(
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if($pet->hasMerit(MeritEnum::AFFECTIONLESS))
            throw new PSPInvalidOperationException($pet->getName() . ' is Affectionless. It cannot gain Merits from affection.');

        if($pet->getAffectionRewardsClaimed() >= $pet->getAffectionLevel())
            throw new PSPInvalidOperationException('You\'ll have to raise ' . $pet->getName() . '\'s affection, first.');

        $meritName = $request->request->get('merit');

        $availableMerits = $em->getRepository(Merit::class)->findBy([ 'name' => MeritFunctions::getAvailableMerits($pet) ]);

        /** @var Merit $merit */
        $merit = ArrayFunctions::find_one($availableMerits, fn(Merit $m) => $m->getName() === $meritName);

        if(!$merit)
            throw new PSPNotFoundException('That merit is not available.');

        $pet
            ->addMerit($merit)
            ->increaseAffectionRewardsClaimed()
        ;

        if($merit->getName() === MeritEnum::SPIRIT_COMPANION)
        {
            $spiritCompanion = new SpiritCompanion();

            $pet->setSpiritCompanion($spiritCompanion);

            $em->persist($spiritCompanion);
        }
        else if($merit->getName() === MeritEnum::VOLAGAMY)
        {
            $pet->setIsFertile(true);
        }

        PetActivityLogFactory::createUnreadLog($em, $pet, '%pet:' . $pet->getId() . '.name% took the "' . $merit->getName() . '" Merit at %user:' . $user->getId() . '.name\'s% suggestion.')
            ->setIcon('ui/merit-icon');

        // you should already unlock the merit when the pet increases in affection, but someone reported that
        // NOT happening, so just in case...
        if(!$pet->getOwner()->hasUnlockedFeature(UnlockableFeatureEnum::Park))
            UserUnlockedFeatureHelpers::create($em, $pet->getOwner(), UnlockableFeatureEnum::Park);

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }

    /**
     * @Route("/{pet}/chooseAffectionReward/skill", methods={"POST"}, requirements={"pet"="\d+"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function chooseAffectionRewardSkill(
        Pet $pet, Request $request, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if($pet->hasMerit(MeritEnum::AFFECTIONLESS))
            throw new PSPInvalidOperationException($pet->getName() . ' is Affectionless. It cannot gain Merits from affection.');

        if($pet->getAffectionRewardsClaimed() >= $pet->getAffectionLevel())
            throw new PSPInvalidOperationException('You\'ll have to raise ' . $pet->getName() . '\'s affection, first.');

        $skillName = $request->request->get('skill');

        if(!PetSkillEnum::isAValue($skillName))
            throw new PSPFormValidationException('"' . $skillName . '" is not a skill!');

        if($pet->getSkills()->getStat($skillName) >= 20)
            throw new PSPInvalidOperationException($pet->getName() . '\'s ' . $skillName . ' is already max!');

        $pet->getSkills()->increaseStat($skillName);
        $pet->increaseAffectionRewardsClaimed();

        PetActivityLogFactory::createUnreadLog($em, $pet, '%pet:' . $pet->getId() . '.name% trained hard in ' . $skillName . ' at %user:' . $user->getId() . '.name\'s% suggestion.')
            ->setIcon('ui/merit-icon');

        $em->flush();

        return $responseService->success($pet, [ SerializationGroupEnum::MY_PET ]);
    }
}
