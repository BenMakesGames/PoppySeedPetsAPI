<?php
namespace App\Controller\Achievement;

use App\Entity\User;
use App\Entity\UserBadge;
use App\Repository\UserBadgeRepository;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/achievement")
 */
final class Completed extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getCompleted(ResponseService $responseService, UserBadgeRepository $userBadgeRepository)
    {
        /** @var User $user */
        $user = $this->getUser();

        $badges = array_map(
            fn(UserBadge $badge) => [
                'badge' => $badge->getBadge(),
                'claimedOn' => $badge->getClaimedOn(),
            ],
            $userBadgeRepository->findBy([
                'user' => $user
            ])
        );

        return $responseService->success($badges);
    }
}