<?php
declare(strict_types=1);

namespace App\Controller\Market;

use App\Entity\Item;
use App\Functions\SimpleDb;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/market")]
class ItemHistoryController extends AbstractController
{
    #[Route("/history/{itemId}", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getItemHistory(int $itemId, ResponseService $responseService)
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
