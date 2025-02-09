<?php
declare(strict_types=1);

namespace App\Controller\Pet;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPPetNotFoundException;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/pet")]
class MeritsController extends AbstractController
{
    #[Route("/{pet}/merits", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getMerits(Pet $pet, ResponseService $responseService)
    {
        /** @var User $user */
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new PSPPetNotFoundException();

        return $responseService->success($pet->getMerits(), [ SerializationGroupEnum::MERIT_ENCYCLOPEDIA ]);
    }
}
