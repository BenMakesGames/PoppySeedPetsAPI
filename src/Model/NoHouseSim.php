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

use App\Entity\Inventory;
use App\Entity\Item;
use App\Service\IRandom;

class NoHouseSim implements IHouseSim
{
    public function getInventoryCount(): int
    {
        NoHouseSim::throwException();
    }

    public function hasInventory(HouseSimRecipe $recipe): bool
    {
        NoHouseSim::throwException();
    }

    public function loseItem(Item|string $item, int $quantity = 1)
    {
        NoHouseSim::throwException();
    }

    /**
     * @param Item[]|string[] $items
     */
    public function loseOneOf(IRandom $rng, array $items): string
    {
        NoHouseSim::throwException();
    }

    public function addInventory(?Inventory $i): bool
    {
        // don't throw an exception here, since inventory can legit be added outside of house hours
        return false;
    }

    public function getInventoryToRemove(): array
    {
        NoHouseSim::throwException();
    }

    public function getInventoryToPersist(): array
    {
        NoHouseSim::throwException();
    }

    private static function throwException()
    {
        throw new \Exception('Ben did a bad programming thing. He\'s been emailed...');
    }
}
