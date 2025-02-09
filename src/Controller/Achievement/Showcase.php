<?php
declare(strict_types=1);

namespace App\Controller\Achievement;

use App\Functions\SimpleDb;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/achievement")]
final class Showcase extends AbstractController
{
    private const PAGE_SIZE = 20;

    #[Route("/showcase", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getShowcase(
        ResponseService $responseService, Request $request
    )
    {
        $db = SimpleDb::createReadOnlyConnection();

        $totalCount = $db->query('SELECT COUNT(DISTINCT(user_id)) FROM user_badge')->getSingleValue();

        $totalPages = (int)ceil($totalCount / self::PAGE_SIZE);

        $page = min($request->query->getInt('page', 0), $totalPages - 1);

        $achievers = SimpleDb::createReadOnlyConnection()
            ->query(
                <<<EOSQL
                    SELECT t.achievementCount, user.id, user.name, user.icon
                    FROM (
                        SELECT user_id, COUNT(id) as achievementCount
                        FROM user_badge
                        GROUP BY user_id
                        ORDER BY achievementCount DESC
                        LIMIT ?,?
                    ) t
                    JOIN user ON user.id = t.user_id
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