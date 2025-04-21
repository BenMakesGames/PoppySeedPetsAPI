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

use App\Entity\Item;
use App\Enum\CostOrYieldTypeEnum;
use Symfony\Component\Serializer\Attribute\Groups;

class TraderOfferCostOrYield
{
    #[Groups(['traderOffer'])]
    public string $type;

    #[Groups(['traderOffer'])]
    public ?Item $item;

    #[Groups(['traderOffer'])]
    public int $quantity;

    public static function createItem(Item $item, int $quantity): TraderOfferCostOrYield
    {
        $costOrYield = new TraderOfferCostOrYield();

        $costOrYield->type = CostOrYieldTypeEnum::ITEM;
        $costOrYield->item = $item;
        $costOrYield->quantity = $quantity;

        return $costOrYield;
    }

    public static function createMoney(int $quantity): TraderOfferCostOrYield
    {
        $costOrYield = new TraderOfferCostOrYield();

        $costOrYield->type = CostOrYieldTypeEnum::MONEY;
        $costOrYield->item = null;
        $costOrYield->quantity = $quantity;

        return $costOrYield;
    }

    public static function createRecyclingPoints(int $quantity): TraderOfferCostOrYield
    {
        $costOrYield = new TraderOfferCostOrYield();

        $costOrYield->type = CostOrYieldTypeEnum::RECYCLING_POINTS;
        $costOrYield->item = null;
        $costOrYield->quantity = $quantity;

        return $costOrYield;
    }
}
