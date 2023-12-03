<?php
namespace App\Controller\HollowEarth;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPNotUnlockedException;
use App\Service\FieldGuideService;
use App\Service\HollowEarthService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/hollowEarth")]
class GetController extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getState(
        ResponseService $responseService, HollowEarthService $hollowEarthService,
        FieldGuideService $fieldGuideService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($user->getHollowEarthPlayer() === null)
            throw new PSPNotUnlockedException('Portal');

        $fieldGuideService->maybeUnlock($user, 'The Hollow Earth', 'You discovered an entrance to the Hollow Earth in your very own home!');

        return $responseService->success($hollowEarthService->getResponseData($user), [ SerializationGroupEnum::HOLLOW_EARTH ]);
    }
}
