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


namespace App\Model;

use App\Entity\User;
use App\Enum\CostOrYieldTypeEnum;
use Symfony\Component\Serializer\Attribute\Groups;

class TraderOffer
{
    #[Groups(['traderOffer'])]
    public string $id;

    /** @var TraderOfferCostOrYield[] */
    #[Groups(['traderOffer'])]
    public array $cost;

    /** @var TraderOfferCostOrYield[] */
    #[Groups(['traderOffer'])]
    public array $yield;

    #[Groups(['traderOffer'])]
    public string $comment;

    #[Groups(['traderOffer'])]
    public int $canMakeExchange;

    #[Groups(['traderOffer'])]
    public bool $lockedToAccount;

    public static function createTradeOffer(
        array $cost, array $yield, string $comment, User $user, array $inventoryQuantitiesByNameAtLocation, bool $lockedToAccount = false
    ): TraderOffer
    {
        $trade = new TraderOffer();

        $trade->id = self::generateID($cost, $yield);
        $trade->cost = $cost;
        $trade->yield = $yield;
        $trade->comment = $comment;
        $trade->canMakeExchange = TraderOffer::getMaxExchanges($cost, $user, $inventoryQuantitiesByNameAtLocation);
        $trade->lockedToAccount = $lockedToAccount;

        return $trade;
    }

    /**
     * @param TraderOfferCostOrYield[] $costs
     * @param ItemQuantity[] $houseInventoryQuantitiesByName
     */
    private static function getMaxExchanges(array $costs, User $user, array $houseInventoryQuantitiesByName): int
    {
        $maxQuantity = 9999;

        foreach($costs as $cost)
        {
            if($cost->type === CostOrYieldTypeEnum::Item)
            {
                if(!array_key_exists($cost->item->getName(), $houseInventoryQuantitiesByName))
                    return 0;

                $maxQuantity = min($maxQuantity, (int)($houseInventoryQuantitiesByName[$cost->item->getName()]->quantity /  $cost->quantity));
            }
            else if($cost->type === CostOrYieldTypeEnum::Money)
                $maxQuantity = min($maxQuantity, (int)($user->getMoneys() / $cost->quantity));
            else if($cost->type === CostOrYieldTypeEnum::RecyclingPoints)
                $maxQuantity = min($maxQuantity, (int)($user->getRecyclePoints() / $cost->quantity));

            if($maxQuantity == 0)
                return 0;
        }

        return $maxQuantity;
    }

    private static function generateID(array $cost, array $yield): string
    {
        $costsAndYields = array_merge($cost, $yield);

        return sha1(implode('&', array_map(function(TraderOfferCostOrYield $coy) {
            return $coy->quantity . 'x' . ($coy->item ? $coy->item->getName() : $coy->type->value);
        }, $costsAndYields)));
    }
}
