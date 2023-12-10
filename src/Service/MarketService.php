<?php
namespace App\Service;

use App\Entity\DailyMarketInventoryTransaction;
use App\Entity\Enchantment;
use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\Spice;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Enum\UserStatEnum;
use App\Functions\InventoryModifierFunctions;
use App\Functions\UserQuestRepository;
use App\Repository\MarketBidRepository;
use App\Repository\MarketListingRepository;
use Doctrine\ORM\EntityManagerInterface;

class MarketService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserStatsService $userStatsRepository,
        private readonly MarketBidRepository $marketBidRepository,
        private readonly TransactionService $transactionService,
        private readonly MarketListingRepository $marketListingRepository
    )
    {
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

    public function updateLowestPriceForInventory(Inventory $inventory)
    {
        $this->updateLowestPriceForItem(
            $inventory->getItem(),
            $inventory->getEnchantment(),
            $inventory->getSpice()
        );
    }

    public function updateLowestPriceForItem(Item $item, ?Enchantment $enchantment, ?Spice $spice)
    {
        $lowestPrice = $this->computeLowestPriceForItem($item, $enchantment, $spice);

        $this->marketListingRepository->upsertLowestPriceForItem($item, $enchantment, $spice, $lowestPrice);
    }

    private function computeLowestPriceForItem(Item $item, ?Enchantment $enchantment, ?Spice $spice): ?int
    {
        $qb = $this->em->createQueryBuilder()
            ->select('MIN(i.sellPrice)')
            ->from(Inventory::class, 'i')
            ->andWhere('i.item = :item')
            ->setParameter('item', $item)
            ->andWhere('i.sellPrice IS NOT NULL')
            ->andWhere('i.sellPrice > 0')
        ;

        if($enchantment)
        {
            $qb = $qb
                ->andWhere('i.enchantment = :enchantment')
                ->setParameter('enchantment', $enchantment)
            ;
        }
        else
            $qb->andWhere('i.enchantment IS NULL');

        if($spice)
        {
            $qb = $qb
                ->andWhere('i.spice = :spice')
                ->setParameter('spice', $spice)
            ;
        }
        else
            $qb->andWhere('i.spice IS NULL');

        $minPrice = (int)$qb->getQuery()->getSingleScalarResult();

        if(!$minPrice)
            return null;

        return $minPrice;
    }

    public function logExchange(Inventory $itemForSale, int $price): DailyMarketInventoryTransaction
    {
        $log = (new DailyMarketInventoryTransaction())
            ->setInventory($itemForSale->getId())
            ->setItem($itemForSale->getItem())
            ->setPrice($price)
        ;

        $this->em->persist($log);

        return $log;
    }

    public function transferItemToPlayer(Inventory $item, User $newOwner, int $location, int $sellPrice)
    {
        $this->userStatsRepository->incrementStat($item->getOwner(), UserStatEnum::TOTAL_MONEYS_EARNED_IN_MARKET, $sellPrice);
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
        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Market) && $user->hasUnlockedFeature(UnlockableFeatureEnum::Museum) && $user->getMaxSellPrice() >= 100)
        {
            $receivedWingedKey = UserQuestRepository::findOrCreate($this->em, $user, 'Received Winged Key', false);

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
        if($price <= 0)
            throw new \InvalidArgumentException('Price must be greater than 0.');

        $highestBid = $this->marketBidRepository->findHighestBidForItem($inventory, Inventory::calculateBuyPrice($price));

        if(!$highestBid)
        {
            $inventory->setSellPrice($price);
            return false;
        }

        $this->logExchange($inventory, $price);

        $user = $inventory->getOwner();

        $this->transactionService->getMoney($user, $price, 'Sold ' . InventoryModifierFunctions::getNameWithModifiers($inventory) . ' in the Market.', [ 'Market' ]);

        $targetLocation = LocationEnum::HOME;

        if($highestBid->getTargetLocation() === LocationEnum::BASEMENT)
        {
            $itemsInBuyersBasement = InventoryService::countTotalInventory($this->em, $highestBid->getUser(), LocationEnum::BASEMENT);

            if($itemsInBuyersBasement < User::MAX_BASEMENT_INVENTORY)
                $targetLocation = LocationEnum::BASEMENT;
        }
        else // assume home as fallback/default
        {
            $itemsInBuyersHome = InventoryService::countTotalInventory($this->em, $highestBid->getUser(), LocationEnum::HOME);

            if($itemsInBuyersHome >= User::MAX_HOUSE_INVENTORY)
            {
                $itemsInBuyersBasement = InventoryService::countTotalInventory($this->em, $highestBid->getUser(), LocationEnum::BASEMENT);

                if($itemsInBuyersBasement < User::MAX_BASEMENT_INVENTORY)
                    $targetLocation = LocationEnum::BASEMENT;
            }
        }

        $this->transferItemToPlayer($inventory, $highestBid->getUser(), $targetLocation, $price);

        if($highestBid->getQuantity() > 1)
            $highestBid->setQuantity($highestBid->getQuantity() - 1);
        else
            $this->em->remove($highestBid);

        return true;
    }

    public function removeMarketListingForItem(int $itemId, int $bonusId, int $spiceId)
    {
        $item = $this->marketListingRepository->findMarketListingForItem($itemId, $bonusId, $spiceId);

        if(!$item)
            return;

        $this->em->remove($item);
    }
}
