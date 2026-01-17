<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Controller\Achievement;

use App\Functions\SimpleDb;
use App\Service\ResponseService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/achievement")]
final class Showcase
{
    private const int PageSize = 20;

    #[Route("/showcase", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getShowcase(
        ResponseService $responseService, Request $request
    ): JsonResponse
    {
        $db = SimpleDb::createReadOnlyConnection();

        $totalCount = $db->query(
            'SELECT COUNT(DISTINCT(user_badge.user_id)) FROM user_badge JOIN user ON user.id = user_badge.user_id WHERE user.disabled_on IS NULL'
        )->getSingleValue();

        $totalPages = (int)ceil($totalCount / self::PageSize);

        $page = min($request->query->getInt('page', 0), $totalPages - 1);

        $achievers = SimpleDb::createReadOnlyConnection()
            ->query(
                <<<EOSQL
                    SELECT t.achievementCount, user.id, user.name, user.icon
                    FROM (
                        SELECT user_badge.user_id, COUNT(user_badge.id) as achievementCount
                        FROM user_badge
                        JOIN user ON user.id = user_badge.user_id
                        WHERE user.disabled_on IS NULL
                        GROUP BY user_badge.user_id
                        ORDER BY achievementCount DESC
                        LIMIT ?,?
                    ) t
                    JOIN user ON user.id = t.user_id
                EOSQL,
                [ $page * self::PageSize, self::PageSize ]
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
            'pageSize' => self::PageSize,
            'pageCount' => $totalPages,
            'page' => $page,
            'resultCount' => $totalCount,
            'unfilteredTotal' => $totalCount,
            'results' => $achievers
        ]);
    }
}