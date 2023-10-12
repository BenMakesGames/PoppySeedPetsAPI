<?php
namespace App\Controller\Achievement;

use App\Entity\User;
use App\Functions\SimpleDb;
use App\Service\PerformanceProfiler;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/achievement")
 */
final class Showcase extends AbstractController
{
    private const PAGE_SIZE = 20;

    /**
     * @Route("/showcase", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getShowcase(
        ResponseService $responseService, Request $request, PerformanceProfiler $performanceProfiler
    )
    {
        $db = SimpleDb::createReadOnlyConnection();

        $time = microtime(true);

        $totalCount = $db->query('SELECT COUNT(DISTINCT(user_id)) FROM user_badge')->getSingleValue();

        $performanceProfiler->logExecutionTime(__METHOD__ . ' - Count', microtime(true) - $time);

        $totalPages = ceil($totalCount / self::PAGE_SIZE);

        $page = min($request->query->getInt('page', 0), $totalPages - 1);

        $time = microtime(true);

        $achievers = SimpleDb::createReadOnlyConnection()
            ->query(
                <<<EOSQL
                    SELECT COUNT(user_badge.id) AS achievementCount, user.id, user.name, user.icon
                    FROM user_badge
                    LEFT JOIN user ON user_badge.user_id = user.id
                    GROUP BY user_badge.user_id
                    LIMIT ?,?
                EOSQL,
                [ $page * self::PAGE_SIZE, self::PAGE_SIZE ]
            )
            ->mapResults(fn($achievementCount, $id, $name, $icon) => [
                'achievementCount' => $achievementCount,
                'resident' => [
                    'id' => $id,
                    'name' => $name,
                    'icon' => $icon,
                ]
            ]);

        $performanceProfiler->logExecutionTime(__METHOD__ . ' - Get Page', microtime(true) - $time);

        return $responseService->success([
            'pageSize' => self::PAGE_SIZE,
            'pageCount' => $totalPages,
            'page' => $page,
            'resultCount' => $totalCount,
            'unfilteredTotal' => $totalCount,
            'results' => $achievers
        ]);
    }
}