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


namespace App\Controller\Market;

use App\Entity\Item;
use App\Functions\SimpleDb;
use App\Service\ResponseService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/market")]
class ItemHistoryController
{
    #[Route("/history/{itemId}", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getItemHistory(int $itemId, ResponseService $responseService): JsonResponse
    {
        $maxAge = \DateInterval::createFromDateString('7 days');

        $db = SimpleDb::createReadOnlyConnection();

        $itemHistory = $db
            ->query(
                'SELECT h.average_price,h.min_price,h.max_price,h.date
                FROM daily_market_item_average AS h
                WHERE h.item_id=:itemId AND h.date>:earliestDate',
                [
                    ':itemId' => $itemId,
                    ':earliestDate' => (new \DateTimeImmutable())->sub($maxAge)->format('Y-m-d')
                ]
            )
            ->mapResults(fn($average_price, $min_price, $max_price, $date) => [
                'averagePrice' => $average_price,
                'minPrice' => $min_price,
                'maxPrice' => $max_price,
                'date' => $date
            ])
        ;

        $lastHistoryItem = $db
            ->query(
                'SELECT h.average_price,h.min_price,h.max_price,h.date
                FROM daily_market_item_average AS h
                WHERE h.item_id=:itemId
                ORDER BY h.date DESC
                LIMIT 1',
                [
                    ':itemId' => $itemId,
                ]
            )
            ->mapResults(fn($average_price, $min_price, $max_price, $date) => [
                'averagePrice' => $average_price,
                'minPrice' => $min_price,
                'maxPrice' => $max_price,
                'date' => $date
            ])
        ;

        return $responseService->success([
            'history' => $itemHistory,
            'lastHistory' => count($lastHistoryItem) == 0 ? null : $lastHistoryItem[0],
        ]);
    }
}
