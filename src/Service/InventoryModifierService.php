<?php
namespace App\Service;

use App\Entity\Inventory;
use Doctrine\ORM\EntityManagerInterface;

class InventoryModifierService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function enchant(Inventory $tool, Inventory $enchantment)
    {
        if($tool->getEnchantment())
            throw new \InvalidArgumentException('That tool already has the "' . $tool->getEnchantment()->getName() . '" bonus. Remove it first if you want to apply a new bonus.');

        $tool->setEnchantment($enchantment->getItem()->getEnchants());

        $this->em->remove($enchantment);
    }

    public function spiceUp(Inventory $food, Inventory $spice)
    {
        if($food->getSpice())
            throw new \InvalidArgumentException('That food is already "' . $food->getSpice()->getName() . '". It can\'t be spiced up any further!');

        $food->setSpice($spice->getItem()->getSpice());

        $this->em->remove($spice);
    }

    public static function getNameWithModifiers(Inventory $item)
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
}
