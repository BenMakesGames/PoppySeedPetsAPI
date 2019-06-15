<?php
namespace App\Service;

use App\Entity\Inventory;
use App\Entity\Pet;
use function App\Functions\array_any;
use Doctrine\ORM\EntityManagerInterface;

class PetService
{
    private $em;
    private $randomService;

    public function __construct(EntityManagerInterface $em, RandomService $randomService)
    {
        $this->em = $em;
        $this->randomService = $randomService;
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

    public function runHour(Pet $pet)
    {
        if($pet->getTime() < 60)
            throw new \InvalidArgumentException('Pet does not have enough Time.');

        $pet->setFood($pet->getFood() - 1);
        $pet->setTime($pet->getTime() - 60);

        if($pet->getWhack() > 0 || $pet->getJunk() > 0)
        {
            if($this->calculateAgeInDays($pet) > 365 * 2)
            {
                // adult tolerance
                $junkDie = 8;
                $whackDie = 12;
            }
            else if($this->calculateAgeInDays($pet) > 365)
            {
                // young adult tolerance
                $junkDie = 12;
                $whackDie = 20;
            }
            else if($this->calculateAgeInDays($pet) > 365 / 2)
            {
                // teen tolerance
                $junkDie = 20;
                $whackDie = 10;
            }
            else
            {
                // kid tolerance
                $junkDie = 12;
                $whackDie = 6;
            }

            if($this->randomService->roll(1, $whackDie) + $this->randomService->roll(1, $junkDie) < $pet->getWhack() + $pet->getJunk())
            {
                // TODO: throw up
                return;
            }

            if($this->randomService->roll(1, 12) < $pet->getWhack())
            {
                // TODO: something whacky?
                return;
            }

            if($this->randomService->roll(1, $junkDie) < $pet->getJunk())
            {
                // TODO: lay around/do something meaningless
                return;
            }
        }
    }

    function calculateAgeInDays(Pet $pet)
    {
        return (new \DateTimeImmutable())->diff($pet->getBirthDate())->days;
    }
}