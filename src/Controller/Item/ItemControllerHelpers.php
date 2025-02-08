<?php
declare(strict_types=1);

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
    public static function validateInventory(?User $user, Inventory $inventory, string $action)
    {
        if(!$user || $user->getId() !== $inventory->getOwner()->getId())
            throw new PSPNotFoundException('That item does not exist.');

        if(!$inventory->getItem()->hasUseAction($action))
            throw new PSPInvalidOperationException('That item cannot be used in that way!');

        if(
            $inventory->getLocation() !== LocationEnum::BASEMENT &&
            $inventory->getLocation() !== LocationEnum::HOME &&
            $inventory->getLocation() !== LocationEnum::MANTLE
        )
            throw new PSPInvalidOperationException('To do this, the item must be in your house, Basement, or Fireplace mantle.');
    }

    public static function validateLocationSpace(Inventory $inventory, EntityManagerInterface $em)
    {
        if($inventory->getLocation() === LocationEnum::HOME)
            self::validateHouseSpace($inventory, $em);
        else if($inventory->getLocation() === LocationEnum::BASEMENT)
            self::validateBasementSpace($inventory, $em);
        else if($inventory->getLocation() === LocationEnum::MANTLE)
            self::validateMantleSpace($inventory, $em);
        else
            throw new PSPInvalidOperationException('To do this, the item must be in your house, Basement, or Fireplace mantle.');
    }

    private static function validateHouseSpace(Inventory $inventory, EntityManagerInterface $em)
    {
        $itemsInHouse = InventoryService::countTotalInventory($em, $inventory->getOwner(), LocationEnum::HOME);

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

    private static function validateBasementSpace(Inventory $inventory, EntityManagerInterface $em)
    {
        $itemsInHouse = InventoryService::countTotalInventory($em, $inventory->getOwner(), LocationEnum::BASEMENT);

        if($itemsInHouse >= User::MAX_BASEMENT_INVENTORY)
            throw new PSPInvalidOperationException('Your basement is already stuffed! You\'ll need to clear some space, or move the ' . $inventory->getItem()->getName() . ' somewhere else before trying again.');
    }

    private static function validateMantleSpace(Inventory $inventory, EntityManagerInterface $em)
    {
        $itemsInHouse = InventoryService::countTotalInventory($em, $inventory->getOwner(), LocationEnum::MANTLE);

        $maxMantleSize = $inventory->getOwner()->getFireplace() ? $inventory->getOwner()->getFireplace()->getMantleSize() : 12;

        if($itemsInHouse >= $maxMantleSize)
            throw new PSPInvalidOperationException('Your Mantle is already packed to the brim! You\'ll need to clear some space, or move the ' . $inventory->getItem()->getName() . ' somewhere else before trying again.');
    }
}
