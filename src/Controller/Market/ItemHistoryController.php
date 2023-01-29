<?php
namespace App\Controller\Market;

use App\Entity\Item;
use App\Enum\SerializationGroupEnum;
use App\Repository\DailyMarketItemAverageRepository;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/market")
 */
class ItemHistoryController extends AbstractController
{
    /**
     * @Route("/history/{item}", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getItemHistory(
        ResponseService $responseService, DailyMarketItemAverageRepository $dailyMarketItemAverageRepository,

        Item $item
    )
    {
        $itemHistory = $dailyMarketItemAverageRepository->findHistoryForItem(
            $item, \DateInterval::createFromDateString('7 days')
        );

        return $responseService->success(
            [
                'history' => $itemHistory,
                'lastHistory' => $dailyMarketItemAverageRepository->findLastHistoryForItem($item)
            ],
            [ SerializationGroupEnum::MARKET_ITEM_HISTORY ]
        );
    }
}
