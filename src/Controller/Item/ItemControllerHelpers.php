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


namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Service\InventoryService;
use Doctrine\ORM\EntityManagerInterface;

class ItemControllerHelpers
{
    public static function validateInventory(?User $user, Inventory $inventory, string $action): void
    {
        if(!$user || $user->getId() !== $inventory->getOwner()->getId())
            throw new PSPNotFoundException('That item does not exist.');

        if(!$inventory->getItem()->hasUseAction($action))
            throw new PSPInvalidOperationException('That item cannot be used in that way!');

        if(
            $inventory->getLocation() !== LocationEnum::Basement &&
            $inventory->getLocation() !== LocationEnum::Home &&
            $inventory->getLocation() !== LocationEnum::Mantle
        )
            throw new PSPInvalidOperationException('To do this, the item must be in your House, Basement, or Fireplace Mantle.');
    }

    public static function validateInventoryAllowingLibrary(?User $user, Inventory $inventory, string $action): void
    {
        if(!$user || $user->getId() !== $inventory->getOwner()->getId())
            throw new PSPNotFoundException('That item does not exist.');

        if(!$inventory->getItem()->hasUseAction($action))
            throw new PSPInvalidOperationException('That item cannot be used in that way!');

        if(
            $inventory->getLocation() !== LocationEnum::Basement &&
            $inventory->getLocation() !== LocationEnum::Home &&
            $inventory->getLocation() !== LocationEnum::Mantle &&
            $inventory->getLocation() !== LocationEnum::Library
        )
            throw new PSPInvalidOperationException('To do this, the item must be in your House, Basement, Fireplace Mantle, or Library.');
    }

    /**
     * @throws PSPInvalidOperationException
     */
    public static function validateLocationSpace(Inventory $inventory, EntityManagerInterface $em): void
    {
        if($inventory->getLocation() === LocationEnum::Home)
            self::validateHouseSpace($inventory, $em);
        else if($inventory->getLocation() === LocationEnum::Basement)
            self::validateBasementSpace($inventory, $em);
        else if($inventory->getLocation() === LocationEnum::Mantle)
            self::validateMantleSpace($inventory, $em);
        else
            throw new PSPInvalidOperationException('To do this, the item must be in your House, Basement, or Fireplace Mantle.');
    }

    private static function validateHouseSpace(Inventory $inventory, EntityManagerInterface $em): void
    {
        $itemsInHouse = InventoryService::countTotalInventory($em, $inventory->getOwner(), LocationEnum::Home);

        if($itemsInHouse > 150)
        {
            $index = $itemsInHouse + $inventory->getOwner()->getId();

            $message = [
                'Whoa! You\'ve already over 150 items?! The server might LITERALLY EXPLODE if I let you open this!',
                'Waitwaitwaitwait... over 150 items? Sorry, you\'re already WAY over the limit!',
                'Whaaaat? You\'re over 150 items already? Dang! You know you\'re technically not supposed to go over 100, right??',
            ][$index % 3];

            throw new PSPInvalidOperationException($message);
        }
    }

    private static function validateBasementSpace(Inventory $inventory, EntityManagerInterface $em): void
    {
        $itemsInHouse = InventoryService::countTotalInventory($em, $inventory->getOwner(), LocationEnum::Basement);

        if($itemsInHouse >= User::MaxBasementInventory)
            throw new PSPInvalidOperationException('Your basement is already stuffed! You\'ll need to clear some space, or move the ' . $inventory->getItem()->getName() . ' somewhere else before trying again.');
    }

    private static function validateMantleSpace(Inventory $inventory, EntityManagerInterface $em): void
    {
        $itemsInHouse = InventoryService::countTotalInventory($em, $inventory->getOwner(), LocationEnum::Mantle);

        $maxMantleSize = $inventory->getOwner()->getFireplace() ? $inventory->getOwner()->getFireplace()->getMantleSize() : 12;

        if($itemsInHouse >= $maxMantleSize)
            throw new PSPInvalidOperationException('Your Mantle is already packed to the brim! You\'ll need to clear some space, or move the ' . $inventory->getItem()->getName() . ' somewhere else before trying again.');
    }
}
