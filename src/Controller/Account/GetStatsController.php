<?php
namespace App\Controller\Account;

use App\Enum\SerializationGroupEnum;
use App\Functions\SimpleDb;
use App\Repository\UserStatsRepository;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/account")
 */
class GetStatsController extends AbstractController
{
    /**
     * @Route("/stats", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
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
