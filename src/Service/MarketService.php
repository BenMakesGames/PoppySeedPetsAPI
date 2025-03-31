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


namespace App\Service;

use App\Entity\DailyMarketInventoryTransaction;
use App\Entity\Inventory;
use App\Entity\InventoryForSale;
use App\Entity\Item;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Enum\UserStatEnum;
use App\Functions\InventoryModifierFunctions;
use App\Functions\MarketListingRepository;
use App\Functions\UserQuestRepository;
use App\Repository\MarketBidRepository;
use Doctrine\ORM\EntityManagerInterface;

class MarketService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserStatsService $userStatsRepository,
        private readonly MarketBidRepository $marketBidRepository,
        private readonly TransactionService $transactionService
    )
    {
    }

    public function getItemToRaiseLimit(User $user): ?array
    {
        return match ($user->getMaxSellPrice())
        {
            10 => [ 'itemName' => 'String', 'hint' => 'Your pets should be able to make some out of Fluff.' ],
            20 => [ 'itemName' => 'Pectin', 'hint' => 'You should be able to extract some from a Red, or Carrot.' ],
            30 => [ 'itemName' => 'Iron Bar', 'hint' => 'Your pets will need to refine some from Iron Ore. When you do get an Iron Bar, please just leave it on the desk over there. Fairies don\'t do iron.' ],
            40 => [ 'itemName' => 'Elvish Magnifying Glass', 'hint' => 'It\'s a magnifying glass made from silver. Your pets should be able to make one.' ],
            50 => [ 'itemName' => 'Gold Key', 'hint' => 'Your pets will need to make one. Out of gold.' ],
            60 => [ 'itemName' => 'Magpie\'s Deal', 'hint' => 'You\'ll have to trade with a magpie. Those birds are pretty smart in a lot of ways, but pretty dumb in others. A lot of people on the island trick them with "Gold" Idols.' ],
            70 => [ 'itemName' => 'Fairy Ring', 'hint' => 'You\'ll need to keep a Fireplace burning for a little while. Talk to some other fairy about getting a Fireplace, if you still don\'t have one.' ],
            80 => [ 'itemName' => 'Antenna', 'hint' => 'Do you not have a Beehive? Well, I guess I\'m not surprised: most bees probably don\'t want to deal with you. I\'d try asking some ants, instead.' ],
            90 => [ 'itemName' => 'Benjamin Franklin', 'hint' => 'Your pets will need to make this. A Silver Key is, well, key to making one. Sorry about the pun. I try to avoid those as much as possible.' ],
            100 => [ 'itemName' => 'Piece of Cetgueli\'s Map', 'hint' => 'I\'d start by looking in sunken treasure chests. It\'s a pirate thing. You know how pirates are.' ],
            110 => [ 'itemName' => 'Blood Wine', 'hint' => 'Oh, this one is actually a little dangerous. One of your pets will have to steal some from a Vampire. Vampires are a bit easier to find in the Umbra, which is already a dangerous place, so, you know: good luck; have fun.' ],
            120 => [ 'itemName' => 'Cheese Omelette with Salsa', 'hint' => 'Really? You take a Cheese Omelette, and you put Salsa on top.' ],
            130 => [ 'itemName' => 'WINE', 'hint' => 'It\'s prepared from a Macintosh, which I believe you can find in Project-E.' ],
            140 => [ 'itemName' => 'Lightning Sword', 'hint' => 'The components should be pretty obvious. As for where to get some lightning, the higher, the better.' ],
            default => null,
        };

    }

    public function updateLowestPriceForItem(Item $item)
    {
        $lowestPrice = $this->computeLowestPriceForItem($item);

        MarketListingRepository::upsertLowestPriceForItem($this->em, $item, $lowestPrice);
    }

    private function computeLowestPriceForItem(Item $item): ?int
    {
        $qb = $this->em->createQueryBuilder()
            ->select('MIN(i.sellPrice)')
            ->from(InventoryForSale::class, 'i')
            ->join('i.inventory', 'inventory')
            ->andWhere('inventory.item = :item')
            ->setParameter('item', $item)
        ;

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

    public function transferItemToPlayer(Inventory $item, User $newOwner, int $location, int $sellPrice, string $newItemComment)
    {
        $this->userStatsRepository->incrementStat($item->getOwner(), UserStatEnum::TOTAL_MONEYS_EARNED_IN_MARKET, $sellPrice);
        $this->userStatsRepository->incrementStat($item->getOwner(), UserStatEnum::ITEMS_SOLD_IN_MARKET, 1);
        $this->userStatsRepository->incrementStat($newOwner, UserStatEnum::ITEMS_BOUGHT_IN_MARKET, 1);

        $item
            ->setSpice(null)
            ->setEnchantment(null)
            ->changeOwner($newOwner, $newItemComment, $this->em)
            ->setLocation($location)
        ;
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

        $highestBid = $this->marketBidRepository->findHighestBidForItem($inventory, InventoryForSale::calculateBuyPrice($price));

        if(!$highestBid)
        {
            if($inventory->getForSale())
            {
                $inventory->getForSale()->setSellPrice($price);
            }
            else
            {
                $forSale = (new InventoryForSale())
                    ->setSellPrice($price);

                $inventory->setForSale($forSale);

                $this->em->persist($forSale);
            }

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

        $this->transferItemToPlayer($inventory, $highestBid->getUser(), $targetLocation, $price, $inventory->getOwner()->getName() . ' sold this to ' . $highestBid->getUser()->getName() . ' in the Market.');

        if($highestBid->getQuantity() > 1)
            $highestBid->setQuantity($highestBid->getQuantity() - 1);
        else
            $this->em->remove($highestBid);

        return true;
    }

    public function removeMarketListingForItem(int $itemId)
    {
        $item = MarketListingRepository::findMarketListingForItem($this->em, $itemId);

        if(!$item)
            return;

        $this->em->remove($item);
    }
}
