<?php
namespace App\Controller\HollowEarth;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\HollowEarthService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/hollowEarth")
 */
class GetController extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getState(ResponseService $responseService, HollowEarthService $hollowEarthService)
    {
        /** @var User $user */
        $user = $this->getUser();

        if($user->getHollowEarthPlayer() === null)
            throw new PSPNotUnlockedException('Portal');

        return $responseService->success($hollowEarthService->getResponseData($user), [ SerializationGroupEnum::HOLLOW_EARTH ]);
    }
}
