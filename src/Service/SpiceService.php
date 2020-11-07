<?php
namespace App\Service;

use App\Entity\Inventory;
use Doctrine\ORM\EntityManagerInterface;

class SpiceService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function spiceUp(Inventory $food, Inventory $spice)
    {
        if($food->getSpice())
            throw new \InvalidArgumentException('That food is already "' . $food->getSpice()->getName() . '". It can\'t be spiced up any further!');

        $food->setSpice($spice->getItem()->getSpice());

        $this->em->remove($spice);
    }

    public function getNameWithSpice(Inventory $food)
    {
        if(!$food->getSpice())
            return $food->getItem()->getName();

        if($food->getSpice()->getIsSuffix())
            return $food->getItem()->getName() . ' ' . $food->getSpice()->getName();

        return $food->getSpice()->getName() . ' ' . $food->getItem()->getName();
    }
}
