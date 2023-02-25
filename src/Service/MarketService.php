<?php
namespace App\Service;

use App\Entity\DailyMarketInventoryTransaction;
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\UserStatEnum;
use App\Functions\InventoryModifierFunctions;
use App\Repository\MarketBidRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use Doctrine\ORM\EntityManagerInterface;

class MarketService
{
    private EntityManagerInterface $em;
    private UserStatsRepository $userStatsRepository;
    private MarketBidRepository $marketBidRepository;
    private InventoryService $inventoryService;
    private TransactionService $transactionService;
    private UserQuestRepository $userQuestRepository;
    private CacheHelper $cacheHelper;

    public function __construct(
        EntityManagerInterface $em, UserStatsRepository  $userStatsRepository, MarketBidRepository $marketBidRepository,
        InventoryService $inventoryService, TransactionService $transactionService,
        UserQuestRepository $userQuestRepository, CacheHelper $cacheHelper
    )
    {
        $this->em = $em;
        $this->userStatsRepository = $userStatsRepository;
        $this->marketBidRepository = $marketBidRepository;
        $this->inventoryService = $inventoryService;
        $this->transactionService = $transactionService;
        $this->userQuestRepository = $userQuestRepository;
        $this->cacheHelper = $cacheHelper;
    }

    public function getItemToRaiseLimit(User $user): ?array
    {
        switch($user->getMaxSellPrice())
        {
            case 10: return [ 'itemName' => 'String', 'hint' => 'Your pets should be able to make some out of Fluff.' ];
            case 20: return [ 'itemName' => 'Pectin', 'hint' => 'You should be able to extract some from a Red, or Carrot.' ];
            case 30: return [ 'itemName' => 'Iron Bar', 'hint' => 'Your pets will need to refine some from Iron Ore. When you do get an Iron Bar, please just leave it on the desk over there. Fairies don\'t do iron.' ];
            case 40: return [ 'itemName' => 'Elvish Magnifying Glass', 'hint' => 'It\'s a magnifying glass made from silver. Your pets should be able to make one.' ];
            case 50: return [ 'itemName' => 'Gold Key', 'hint' => 'Your pets will need to make one. Out of gold.' ];
            case 60: return [ 'itemName' => 'Magpie\'s Deal', 'hint' => 'You\'ll have to trade with a magpie. Those birds are pretty smart in a lot of ways, but pretty dumb in others. A lot of people on the island trick them with "Gold" Idols.' ];
            case 70: return [ 'itemName' => 'Fairy Ring', 'hint' => 'You\'ll need to keep a Fireplace burning for a little while. Talk to some other fairy about getting a Fireplace, if you still don\'t have one.' ];
            case 80: return [ 'itemName' => 'Antenna', 'hint' => 'Do you not have a Beehive? Well, I guess I\'m not surprised: most bees probably don\'t want to deal with you. I\'d try asking some ants, instead.' ];
            case 90: return [ 'itemName' => 'Benjamin Franklin', 'hint' => 'Your pets will need to make this. A Silver Key is, well, key to making one. Sorry about the pun. I try to avoid those as much as possible.' ];
            case 100: return [ 'itemName' => 'Piece of Cetgueli\'s Map', 'hint' => 'I\'d start by looking in sunken treasure chests. It\'s a pirate thing. You know how pirates are.' ];
            case 110: return [ 'itemName' => 'Blood Wine', 'hint' => 'Oh, this one is actually a little dangerous. One of your pets will have to steal some from a Vampire. Vampires are a bit easier to find in the Umbra, which is already a dangerous place, so, you know: good luck; have fun.' ];
            case 120: return [ 'itemName' => 'Cheese Omelette with Salsa', 'hint' => 'Really? You take a Cheese Omelette, and you put Salsa on top.' ];
            case 130: return [ 'itemName' => 'WINE', 'hint' => 'It\'s prepared from a Macintosh, which I believe you can find in Project-E.' ];
            case 140: return [ 'itemName' => 'Lightning Sword', 'hint' => 'The components should be pretty obvious. As for where to get some lightning, the higher, the better.' ];
        }

        return null;
    }

    private static function getLowestPriceCacheKey(Inventory $inventory): string
    {
        return
            'Market Lowest Price ' .
            $inventory->getItem()->getId() . ',' .
            ($inventory->getEnchantment() ? $inventory->getEnchantment()->getId() : 'null') . ',' .
            ($inventory->getSpice() ? $inventory->getSpice()->getId() : 'null')
        ;
    }

    public function getLowestPriceForItem(Inventory $inventory)
    {
        $cacheKey = self::getLowestPriceCacheKey($inventory);

        return $this->cacheHelper->getOrCompute(
            $cacheKey,
            \DateInterval::createFromDateString('24 hours'),
            fn() => $this->computeLowestPriceForItem($inventory)
        );
    }

    public function updateLowestPriceForItem(Inventory $inventory)
    {
        $cacheKey = self::getLowestPriceCacheKey($inventory);

        $this->cacheHelper->set(
            $cacheKey,
            \DateInterval::createFromDateString('24 hours'),
            $this->computeLowestPriceForItem($inventory)
        );
    }

    public function computeLowestPriceForItem(Inventory $inventory)
    {
        return (int)$this->em->createQueryBuilder()
            ->select('MIN(i.buyPrice)')
            ->from(Inventory::class, 'i')
            ->where('i.item = :item')
            ->andWhere('i.enchantment = :enchantment')
            ->andWhere('i.spice = :spice')
            ->andWhere('i.buyPrice IS NOT NULL')
            ->setParameter('item', $inventory->getItem())
            ->setParameter('enchantment', $inventory->getEnchantment())
            ->setParameter('spice', $inventory->getSpice())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function logExchange(Inventory $itemForSale): DailyMarketInventoryTransaction
    {
        $log = (new DailyMarketInventoryTransaction())
            ->setInventory($itemForSale->getId())
            ->setItem($itemForSale->getItem())
            ->setPrice($itemForSale->getBuyPrice())
        ;

        $this->em->persist($log);

        return $log;
    }

    public function transferItemToPlayer(Inventory $item, User $newOwner, int $location)
    {
        $this->userStatsRepository->incrementStat($item->getOwner(), UserStatEnum::TOTAL_MONEYS_EARNED_IN_MARKET, $item->getSellPrice());
        $this->userStatsRepository->incrementStat($item->getOwner(), UserStatEnum::ITEMS_SOLD_IN_MARKET, 1);
        $this->userStatsRepository->incrementStat($newOwner, UserStatEnum::ITEMS_BOUGHT_IN_MARKET, 1);

        $item
            ->setOwner($newOwner)
            ->setSellPrice(null)
            ->setLocation($location)
            ->setModifiedOn()
        ;

        if($item->getLunchboxItem())
            $this->em->remove($item->getLunchboxItem());

        if($item->getHolder())
            $item->getHolder()->setTool(null);

        if($item->getWearer())
            $item->getWearer()->setHat(null);
    }

    public function canOfferWingedKey(User $user)
    {
        if($user->getUnlockedMarket() && $user->getUnlockedMuseum() && $user->getMaxSellPrice() >= 100)
        {
            $receivedWingedKey = $this->userQuestRepository->findOrCreate($user, 'Received Winged Key', false);

            if(!$receivedWingedKey->getValue())
                return true;
        }

        return false;
    }

    /**
     * @return bool "true" if the item was scooped up by a bidder
     */
    public function sell(Inventory $inventory, int $price): bool
    {
        $user = $inventory->getOwner();

        if($price <= 0)
        {
            $inventory->setSellPrice(null);
            return false;
        }

        $inventory->setSellPrice($price);

        $highestBid = $this->marketBidRepository->findHighestBidForItem($inventory, Inventory::calculateBuyPrice($price));

        if(!$highestBid)
            return false;

        $this->logExchange($inventory);

        $this->transactionService->getMoney($user, $price, 'Sold ' . InventoryModifierFunctions::getNameWithModifiers($inventory) . ' in the Market.', [ 'Market' ]);

        $targetLocation = LocationEnum::HOME;

        if($highestBid->getTargetLocation() === LocationEnum::BASEMENT)
        {
            $itemsInBuyersBasement = $this->inventoryService->countTotalInventory($highestBid->getUser(), LocationEnum::BASEMENT);

            if($itemsInBuyersBasement < User::MAX_BASEMENT_INVENTORY)
                $targetLocation = LocationEnum::BASEMENT;
        }
        else // assume home as fallback/default
        {
            $itemsInBuyersHome = $this->inventoryService->countTotalInventory($highestBid->getUser(), LocationEnum::HOME);

            if($itemsInBuyersHome >= User::MAX_HOUSE_INVENTORY)
            {
                $itemsInBuyersBasement = $this->inventoryService->countTotalInventory($highestBid->getUser(), LocationEnum::BASEMENT);

                if($itemsInBuyersBasement < User::MAX_BASEMENT_INVENTORY)
                    $targetLocation = LocationEnum::BASEMENT;
            }
        }

        $this->transferItemToPlayer($inventory, $highestBid->getUser(), $targetLocation);

        if($highestBid->getQuantity() > 1)
            $highestBid->setQuantity($highestBid->getQuantity() - 1);
        else
            $this->em->remove($highestBid);

        return true;
    }
}
