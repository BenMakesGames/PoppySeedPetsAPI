<?php
namespace App\Controller\HollowEarth;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Exceptions\PSPPetNotFoundException;
use App\Service\HollowEarthService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/hollowEarth")]
class ChangePetController extends AbstractController
{
    #[Route("/changePet/{pet}", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function changePet(
        Pet $pet, ResponseService $responseService, EntityManagerInterface $em, HollowEarthService $hollowEarthService
    )
    {
        /** @var User $user */
        $user = $this->getUser();
        $player = $user->getHollowEarthPlayer();

        if($player === null)
            throw new PSPNotUnlockedException('Hollow Earth');

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        if($player->getCurrentAction() !== null || $player->getMovesRemaining() > 0)
            throw new PSPInvalidOperationException('Pet cannot be changed at this time.');

        $player->setChosenPet($pet);

        $em->flush();

        return $responseService->success($hollowEarthService->getResponseData($user), [ SerializationGroupEnum::HOLLOW_EARTH ]);
    }
}
