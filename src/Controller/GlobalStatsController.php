<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\DailyStats;
use App\Enum\SerializationGroupEnum;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/globalStats")]
class GlobalStatsController extends AbstractController
{
    #[Route("/today", methods: ["GET"])]
    public function getToday(EntityManagerInterface $em, ResponseService $responseService)
    {
        return $responseService->success(
            $em->getRepository(DailyStats::class)->findBy([], [ 'id' => 'desc' ], 30),
            [ SerializationGroupEnum::GLOBAL_STATS ]
        );
    }
}
