<?php
namespace App\Controller\Badge;

use App\Entity\User;
use App\Enum\BadgeEnum;
use App\Enum\UserStatEnum;
use App\Repository\UserBadgeRepository;
use App\Repository\UserStatsRepository;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/badge")
 */
final class Available extends AbstractController
{
    /**
     * @Route("/available", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getAvailable(
        UserBadgeRepository $userBadgeRepository, UserStatsRepository $userStatsRepository,
        ResponseService $responseService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        $claimed = $userBadgeRepository->getClaimedBadgeNames($user);

        $unclaimed = array_diff(BadgeEnum::getValues(), $claimed);

        $info = [];

        foreach($unclaimed as $badge)
            $info[] = self::getBadgeProgress($badge, $user, $userStatsRepository);

        return $responseService->success($info);
    }

    private static function getBadgeProgress(string $badge, User $user, UserStatsRepository $userStatsRepository): array
    {
        switch($badge)
        {
            case BadgeEnum::BAABBLES_OPENED_10:
                $progress = [ 'target' => 10, 'current' => $userStatsRepository->getStatTotal($user, []) ];
                break;

            case BadgeEnum::BAABBLES_OPENED_100:
                $progress = [ 'target' => 100, 'current' => $userStatsRepository->getStatTotal($user, []) ];
                break;

            case BadgeEnum::BAABBLES_OPENED_1000:
                $progress = [ 'target' => 1000, 'current' => $userStatsRepository->getStatTotal($user, []) ];
                break;

            default:
                throw new \Exception('Oops! Badge not implemented! Ben was a bad programmer!');
        }

        return [
            'badge' => $badge,
            'progress' => $progress
        ];
    }
}