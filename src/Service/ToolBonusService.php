<?php
namespace App\Service;

use App\Entity\Inventory;
use Doctrine\ORM\EntityManagerInterface;

class ToolBonusService
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

    public function getNameWithBonus(Inventory $tool)
    {
        if(!$tool->getEnchantment())
            return $tool->getItem()->getName();

        if($tool->getEnchantment()->getIsSuffix())
            return $tool->getItem()->getName() . ' ' . $tool->getEnchantment()->getName();

        return $tool->getEnchantment()->getName() . ' ' . $tool->getItem()->getName();
    }
}
