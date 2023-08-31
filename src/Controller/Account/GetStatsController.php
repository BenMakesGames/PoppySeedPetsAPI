<?php
namespace App\Controller\Account;

use App\Enum\SerializationGroupEnum;
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
    public function getStats(ResponseService $responseService, UserStatsRepository $userStatsRepository)
    {
        $stats = $userStatsRepository->findBy([ 'user' => $this->getUser() ]);

        return $responseService->success($stats, [ SerializationGroupEnum::MY_STATS ]);
    }
}
