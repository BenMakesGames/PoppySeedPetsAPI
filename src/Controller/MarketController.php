<?php
namespace App\Controller;

use App\Entity\Inventory;
use App\Entity\Item;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Repository\DailyMarketItemAverageRepository;
use App\Repository\InventoryRepository;
use App\Repository\UserStatsRepository;
use App\Service\InventoryModifierService;
use App\Service\Filter\MarketFilterService;
use App\Service\Filter\TransactionFilterService;
use App\Service\InventoryService;
use App\Service\MarketService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/market")
 */
class MarketController extends PoppySeedPetsController
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
            SerializationGroupEnum::MARKET_ITEM_HISTORY
        );
    }

    /**
     * @Route("/search", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function search(Request $request, ResponseService $responseService, MarketFilterService $marketFilterService)
    {
        $marketFilterService->setUser($this->getUser());

        return $responseService->success(
            $marketFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MARKET_ITEM ]
        );
    }

    /**
     * @Route("/limits", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getMarketLimits(ResponseService $responseService, MarketService $marketService)
    {
        $user = $this->getUser();

        return $responseService->success([
            'moneysLimit' => $user->getMaxSellPrice(),
            'itemRequired' => $marketService->getItemToRaiseLimit($user)
        ]);
    }

    /**
     * @Route("/limits/increase", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function increaseMarketLimits(
        ResponseService $responseService, MarketService $marketService, InventoryService $inventoryService,
        EntityManagerInterface $em
    )
    {
        $user = $this->getUser();

        $itemRequired = $marketService->getItemToRaiseLimit($user);

        if(!$itemRequired)
            throw new UnprocessableEntityHttpException('The market limits don\'t go any higher!');

        if($inventoryService->loseItem($itemRequired['itemName'], $user, [ LocationEnum::HOME, LocationEnum::BASEMENT ], 1) === 0)
            throw new UnprocessableEntityHttpException('Come back when you ACTUALLY have the item.');

        $user->setMaxSellPrice($user->getMaxSellPrice() + 10);

        $em->flush();

        return $responseService->success([
            'moneysLimit' => $user->getMaxSellPrice(),
            'itemRequired' => $marketService->getItemToRaiseLimit($user)
        ]);
    }

    /**
     * @Route("/buy", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function buy(
        Request $request, ResponseService $responseService, AdapterInterface $cache, EntityManagerInterface $em,
        UserStatsRepository $userStatsRepository, InventoryRepository $inventoryRepository, Squirrel3 $squirrel3,
        TransactionService $transactionService, MarketService $marketService, InventoryModifierService $bonusService
    )
    {
        $user = $this->getUser();

        $itemId = $request->request->getInt('item', 0);
        $bonusId = $request->request->getInt('bonus', 0);
        $spiceId = $request->request->getInt('spice', 0);
        $price = $request->request->getInt('sellPrice', 0);

        if($itemId === 0 || $price === 0)
            throw new UnprocessableEntityHttpException('Item and price are both required.');

        if(Inventory::calculateBuyPrice($price) > $user->getMoneys())
            throw new UnprocessableEntityHttpException('You do not have enough moneys.');

        $itemsAtHome = $inventoryRepository->countItemsInLocation($user, LocationEnum::HOME);
        $placeItemsIn = LocationEnum::HOME;

        if($itemsAtHome >= 100)
        {
            if(!$user->getUnlockedBasement())
                throw new UnprocessableEntityHttpException('Your house has ' . $itemsAtHome . ' items; you\'ll need to make some space, first!');

            $itemsInBasement = $inventoryRepository->countItemsInLocation($user, LocationEnum::BASEMENT);

            $dang = $squirrel3->rngNextFromArray([
                'Dang!',
                'Goodness!',
                'Great googly-moogly!',
                'Whaaaat?!',
            ]);

            if($itemsInBasement >= 10000)
                throw new UnprocessableEntityHttpException('Your house has ' . $itemsAtHome . ', and your basement has ' . $itemsInBasement . ' items! (' . $dang . ') You\'ll need to make some space, first...');

            $placeItemsIn = LocationEnum::BASEMENT;
        }

        $qb = $inventoryRepository->createQueryBuilder('i')
            ->andWhere('i.owner!=:user')
            ->andWhere('i.sellPrice<=:price')
            ->andWhere('i.item=:item')
            ->addOrderBy('i.sellPrice', 'ASC')
            ->addOrderBy('i.sellListDate', 'ASC')
            ->setParameter('user', $user->getId())
            ->setParameter('price', $price)
            ->setParameter('item', $itemId)
        ;

        if($bonusId)
        {
            $qb = $qb
                ->andWhere('i.enchantment=:bonusId')
                ->setParameter('bonusId', $bonusId)
            ;
        }
        else
        {
            $qb = $qb->andWhere('i.enchantment IS NULL');
        }

        if($spiceId)
        {
            $qb = $qb
                ->andWhere('i.spice=:spiceId')
                ->setParameter('spiceId', $spiceId)
            ;
        }
        else
        {
            $qb = $qb->andWhere('i.spice IS NULL');
        }

        /** @var Inventory[] $forSale */
        $forSale = $qb->getQuery()->getResult();

        $forSale = array_filter($forSale, function(Inventory $inventory) use($cache) {
            $item = $cache->getItem('Trading Inventory #' . $inventory->getId());

            return !$item->isHit();
        });

        if(count($forSale) === 0)
            throw new NotFoundHttpException('An item for that price could not be found on the market. Someone may have bought it up just for you did! Sorry :| Reload the page to get the latest prices available!');

        $itemToBuy = ArrayFunctions::min($forSale, function(Inventory $inventory) {
            return $inventory->getSellPrice();
        });

        $item = $cache->getItem('Trading Inventory #' . $itemToBuy->getId());
        $item->set(true)->expiresAfter(\DateInterval::createFromDateString('1 minute'));
        $cache->save($item);

        try
        {
            $transactionService->getMoney($itemToBuy->getOwner(), $itemToBuy->getSellPrice(), 'Sold ' . $bonusService->getNameWithModifiers($itemToBuy) . ' in the Market.');
            $userStatsRepository->incrementStat($itemToBuy->getOwner(), UserStatEnum::TOTAL_MONEYS_EARNED_IN_MARKET, $itemToBuy->getSellPrice());
            $userStatsRepository->incrementStat($itemToBuy->getOwner(), UserStatEnum::ITEMS_SOLD_IN_MARKET, 1);

            $transactionService->spendMoney($user, $itemToBuy->getBuyPrice(), 'Bought ' . $bonusService->getNameWithModifiers($itemToBuy) . ' in the Market.');
            $userStatsRepository->incrementStat($user, UserStatEnum::ITEMS_BOUGHT_IN_MARKET, 1);

            $marketService->logExchange($itemToBuy);

            $marketService->transferItemToPlayer($itemToBuy, $user, $placeItemsIn);

            $em->flush();
        }
        finally
        {
            $cache->deleteItem('Trading Inventory #' . $itemToBuy->getId());
        }

        return $responseService->success($itemToBuy, SerializationGroupEnum::MY_INVENTORY);
    }

    /**
     * @Route("/transactionHistory", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function history(Request $request, ResponseService $responseService, TransactionFilterService $transactionFilterService)
    {
        $transactionFilterService->setUser($this->getUser());

        return $responseService->success(
            $transactionFilterService->getResults($request->query),
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::MY_TRANSACTION ]
        );
    }
}
