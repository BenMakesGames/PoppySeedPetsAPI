<?php
namespace App\Functions;

use App\Entity\Enchantment;
use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\Spice;
use Doctrine\ORM\EntityManagerInterface;

class InventoryModifierFunctions
{
    public static function enchant(EntityManagerInterface $em, Inventory $tool, Inventory $enchantment)
    {
        if($tool->getEnchantment())
            throw new \InvalidArgumentException('That tool already has the "' . $tool->getEnchantment()->getName() . '" bonus. Remove it first if you want to apply a new bonus.');

        $tool->setEnchantment($enchantment->getItem()->getEnchants());

        $em->remove($enchantment);
    }

    public static function spiceUp(EntityManagerInterface $em, Inventory $food, Inventory $spice)
    {
        if($food->getSpice())
            throw new \InvalidArgumentException('That food is already "' . $food->getSpice()->getName() . '". It can\'t be spiced up any further!');

        $food->setSpice($spice->getItem()->getSpice());

        $em->remove($spice);
    }

    public static function getNameWithModifiers(Inventory $item): ?string
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
