<?php
namespace App\Controller\Zoologist;

use App\Entity\User;
use App\Entity\UserSpeciesCollected;
use App\Enum\UnlockableFeatureEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Exceptions\PSPPetNotFoundException;
use App\Repository\PetRepository;
use App\Repository\UserSpeciesCollectedRepository;
use App\Repository\UserStatsRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/zoologist")
 */
class ShowPetController extends AbstractController
{
    /**
     * @Route("/showPet", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function showPet(
        EntityManagerInterface $em, Request $request, UserSpeciesCollectedRepository $userSpeciesCollectedRepository,
        PetRepository $petRepository, UserStatsRepository $userStatsRepository, ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if(!$user->hasUnlockedFeature(UnlockableFeatureEnum::Zoologist))
            throw new PSPNotUnlockedException('Museum');

        $petId = $request->request->getInt('petId');

        if($petId <= 0)
            throw new PSPFormValidationException('Which pet?');

        $pet = $petRepository->findOneBy([
            'id' => $petId,
            'owner' => $user->getId()
        ]);

        if(!$pet)
            throw new PSPPetNotFoundException();

        $alreadyDiscovered = $userSpeciesCollectedRepository->findOneBy([
            'user' => $user->getId(),
            'species' => $pet->getSpecies()->getId()
        ]);

        if($alreadyDiscovered)
            throw new PSPInvalidOperationException('Hm? The ' . $pet->getSpecies()->getName() . '? You\'ve shown one to me before.');

        $discovery = (new UserSpeciesCollected())
            ->setUser($user)
            ->setSpecies($pet->getSpecies())
            ->setPetName($pet->getName())
            ->setColorA($pet->getColorA())
            ->setColorB($pet->getColorB())
            ->setScale($pet->getScale())
        ;

        $em->persist($discovery);

        $userStatsRepository->incrementStat($user, 'Species Cataloged');

        $em->flush();

        return $responseService->success();
    }
}