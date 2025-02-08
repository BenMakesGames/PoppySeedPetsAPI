<?php
declare(strict_types=1);

namespace App\Controller\Style;

use App\Entity\User;
use App\Enum\SerializationGroupEnum;
use App\Service\Filter\UserStyleFilter;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/style")]
class GetFollowedController extends AbstractController
{
    #[Route("/following", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getThemesOfFollowedPlayers(
        Request $request, UserStyleFilter $userStyleFilter, ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $userStyleFilter->setUser($user);

        $themes = $userStyleFilter->getResults($request->query);

        return $responseService->success($themes, [
            SerializationGroupEnum::FILTER_RESULTS,
            SerializationGroupEnum::PUBLIC_STYLE,
        ]);
    }
}
