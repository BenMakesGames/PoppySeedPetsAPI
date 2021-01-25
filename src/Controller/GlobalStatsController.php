<?php
namespace App\Controller;

use App\Enum\SerializationGroupEnum;
use App\Repository\DailyStatsRepository;
use App\Service\ResponseService;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/globalStats")
 */
class GlobalStatsController extends PoppySeedPetsController
{
    /**
     * @Route("/today", methods={"GET"})
     */
    public function getToday(
        DailyStatsRepository $dailyStatsRepository, ResponseService $responseService
    )
    {
        return $responseService->success(
            $dailyStatsRepository->findOneBy([], [ 'id' => 'desc' ]),
            SerializationGroupEnum::GLOBAL_STATS
        );
    }
}
