<?php
namespace App\Controller\Pet;

use App\Controller\PoppySeedPetsController;
use App\Entity\Pet;
use App\Enum\SerializationGroupEnum;
use App\Service\ResponseService;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/pet")
 */
class MeritsController extends PoppySeedPetsController
{
    /**
     * @Route("/{pet}/merits", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getMerits(Pet $pet, ResponseService $responseService)
    {
        $user = $this->getUser();

        if($pet->getOwner()->getId() !== $user->getId())
            throw new AccessDeniedHttpException('That\'s not your pet.');

        return $responseService->success($pet->getMerits(), [ SerializationGroupEnum::MERIT_ENCYCLOPEDIA ]);
    }
}
