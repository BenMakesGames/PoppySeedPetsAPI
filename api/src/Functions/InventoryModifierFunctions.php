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

namespace App\Functions;

use App\Entity\Enchantment;
use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\Spice;
use App\Exceptions\PSPInvalidOperationException;
use Doctrine\ORM\EntityManagerInterface;

class InventoryModifierFunctions
{
    public static function enchant(EntityManagerInterface $em, Inventory $tool, Inventory $enchantment): void
    {
        if($tool->getEnchantment())
            throw new PSPInvalidOperationException('That tool already has the "' . $tool->getEnchantment()->getName() . '" bonus. Remove it first if you want to apply a new bonus.');

        $tool->setEnchantment($enchantment->getItem()->getEnchants());

        $em->remove($enchantment);
    }

    public static function spiceUp(EntityManagerInterface $em, Inventory $food, Inventory $spice): void
    {
        if($food->getSpice())
            throw new PSPInvalidOperationException('That food is already "' . $food->getSpice()->getName() . '". It can\'t be spiced up any further!');

        $food->setSpice($spice->getItem()->getSpice());

        $em->remove($spice);
    }

    public static function getNameWithModifiers(Inventory $item): string
    {
        if(!$item->getEnchantment() && !$item->getSpice())
            return $item->getItem()->getName();

        $nameParts = [];

        if($item->getEnchantment() && !$item->getEnchantment()->getIsSuffix())
            $nameParts[] = $item->getEnchantment()->getName();

        if($item->getSpice() && !$item->getSpice()->getIsSuffix())
            $nameParts[] = $item->getSpice()->getName();

        $nameParts[] = $item->getItem()->getName();

        if($item->getEnchantment() && $item->getEnchantment()->getIsSuffix())
            $nameParts[] = $item->getEnchantment()->getName();

        if($item->getSpice() && $item->getSpice()->getIsSuffix())
            $nameParts[] = $item->getSpice()->getName();

        return implode(' ', $nameParts);
    }

    public static function getNameWithModifiersForItem(Item $item, ?Enchantment $enchantment, ?Spice $spice): ?string
    {
        if(!$enchantment && !$item->getSpice())
            return $item->getName();

        $nameParts = [];

        if($enchantment && !$enchantment->getIsSuffix())
            $nameParts[] = $enchantment->getName();

        if($spice && !$spice->getIsSuffix())
            $nameParts[] = $spice->getName();

        $nameParts[] = $item->getName();

        if($enchantment && $enchantment->getIsSuffix())
            $nameParts[] = $enchantment->getName();

        if($spice && $spice->getIsSuffix())
            $nameParts[] = $spice->getName();

        return implode(' ', $nameParts);
    }
}
