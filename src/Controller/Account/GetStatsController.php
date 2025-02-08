<?php
declare(strict_types=1);

namespace App\Controller\Account;

use App\Functions\SimpleDb;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/account")]
class GetStatsController extends AbstractController
{
    #[Route("/stats", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getStats(ResponseService $responseService)
    {
        $stats = SimpleDb::createReadOnlyConnection()
            ->query(
                'SELECT stat,value,first_time AS firstTime,last_time AS lastTime FROM user_stats WHERE user_id = ?',
                [ $this->getUser()->getId() ]
            )
            ->getResults()
        ;

        return $responseService->success($stats);
    }
}
