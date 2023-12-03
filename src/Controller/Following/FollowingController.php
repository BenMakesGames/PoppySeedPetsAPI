<?php
namespace App\Controller\Following;

use App\Enum\SerializationGroupEnum;
use App\Service\Filter\UserFilterService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Annotations\DoesNotRequireHouseHours;

#[Route("/following")]
class FollowingController extends AbstractController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("", methods={"GET"})
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function search(
        Request $request, ResponseService $responseService, UserFilterService $userFilterService
    )
    {
        $user = $this->getUser();

        $userFilterService->setUser($user);
        $userFilterService->addDefaultFilter('followedBy', $user->getId());

        return $responseService->success(
            $userFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::USER_PUBLIC_PROFILE ]
        );
    }
}
