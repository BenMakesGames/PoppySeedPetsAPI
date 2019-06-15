<?php
namespace App\Service;

use App\Entity\Inventory;
use App\Entity\Pet;
use function App\Functions\array_any;
use Doctrine\ORM\EntityManagerInterface;

class PetService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param Inventory[] $inventory
     */
    public function feedPet(Pet $pet, array $inventory)
    {
        if($pet->getIsDead())
            throw new \InvalidArgumentException($pet->getName() . ' is dead :|');

        if(array_any($inventory, function(Inventory $i) { return $i->getItem()->getFood() !== null; }))
            throw new \InvalidArgumentException('At least one of the items selected is not edible!');

        \shuffle($inventory);

        foreach($inventory as $i)
        {
            $food = $i->getItem()->getFood();

            if($food->food) $pet->setFood($pet->getFood() + $food->food);
            if($food->love) $pet->setLove($pet->getLove() + $food->love);
            //if($food->junk) $pet->setJunk($pet->getJunk() + $food->junk);

            $this->em->remove($i);
        }
    }
}