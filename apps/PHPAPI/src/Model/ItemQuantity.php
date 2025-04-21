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
use Symfony\Component\Serializer\Attribute\Groups;

class ItemQuantity
{
    #[Groups(['myInventory', 'knownRecipe'])]
    public Item $item;

    #[Groups(['myInventory', 'knownRecipe'])]
    public int $quantity;

    /**
     * @param ItemQuantity[] $quantities
     * @return ItemQuantity[]
     */
    public static function divide(array $quantities, int $divisor): array
    {
        $dividedQuantities = [];

        foreach($quantities as $quantity)
        {
            if($quantity->quantity % $divisor !== 0)
                throw new \InvalidArgumentException('$quantities cannot be evenly divided by $divisor (' . $divisor . ')');

            $q = new ItemQuantity();
            $q->item = $quantity->item;
            $q->quantity = $quantity->quantity / $divisor;

            $dividedQuantities[] = $q;
        }

        return $dividedQuantities;
    }
}
