<?php
namespace App\Service;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use function App\Functions\array_any;
use function App\Functions\array_list;
use App\Model\PetChanges;
use App\Model\PetChangesSummary;
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

    public function doPet(Pet $pet): PetActivityLog
    {
        $now = new \DateTimeImmutable();

        if($pet->getIsDead())
            throw new \InvalidArgumentException($pet->getName() . ' is dead :|');

        if($pet->getLastInteracted() < $now->modify('-48 hours'))
            $pet->setLastInteracted($now->modify('-24 hours'));
        else if($pet->getLastInteracted() < $now->modify('-24 hours'))
            $pet->setLastInteracted($now->modify('-15 minutes'));
        else if($pet->getLastInteracted() < $now->modify('-15 minutes'))
            $pet->setLastInteracted($now);
        else
            throw new \InvalidArgumentException('You\'ve already interacted with this pet recently.');

        $changes = new PetChanges($pet);

        $pet->increaseSafety(1);
        $pet->increaseLove(1);

        return $this->logActivity($pet, 'You pet ' . $pet->getName(). '.', $changes->compare($pet));
    }

    public function doPraise(Pet $pet): PetActivityLog
    {
        $now = new \DateTimeImmutable();

        if($pet->getIsDead())
            throw new \InvalidArgumentException($pet->getName() . ' is dead :|');

        if($pet->getLastInteracted() < $now->modify('-48 hours'))
            $pet->setLastInteracted($now->modify('-24 hours'));
        else if($pet->getLastInteracted() < $now->modify('-24 hours'))
            $pet->setLastInteracted($now->modify('-15 minutes'));
        else if($pet->getLastInteracted() < $now->modify('-15 minutes'))
            $pet->setLastInteracted($now);
        else
            throw new \InvalidArgumentException('You\'ve already interacted with this pet recently.');

        $changes = new PetChanges($pet);

        $pet->increaseLove(1);
        $pet->increaseEsteem(1);

        return $this->logActivity($pet, 'You praised ' . $pet->getName(). '.', $changes->compare($pet));
    }

    /**
     * @param Inventory[] $inventory
     */
    public function doFeed(Pet $pet, array $inventory): PetActivityLog
    {
        if($pet->getIsDead())
            throw new \InvalidArgumentException($pet->getName() . ' is dead :|');

        if(array_any($inventory, function(Inventory $i) { return $i->getItem()->getFood() !== null; }))
            throw new \InvalidArgumentException('At least one of the items selected is not edible!');

        \shuffle($inventory);

        $petChanges = new PetChanges($pet);
        $foodsEaten = [];

        foreach($inventory as $i)
        {
            $food = $i->getItem()->getFood();

            if($food->junk) $pet->increaseJunk($food->junk);
            if($food->whack) $pet->increaseWhack($food->whack);
            if($food->food) $pet->increaseFood($food->food);

            if($pet->getFood() + $pet->getWhack() - $pet->getJunk() > 0)
                if($food->love) $pet->increaseLove($food->love);

            $this->em->remove($i);

            $foodsEaten[] = $i->getItem()->getName();

            if($pet->getJunk() + $pet->getWhack() + $pet->getFood() >= $pet->getStomachSize())
                break;
        }

        // gain love & safety equal to 1/8 food gained, when hand-fed
        $foodGained = $pet->getFood() - $petChanges->food;

        if($foodGained > 0)
        {
            $remainder = $foodGained % 8;
            $gain = floor($foodGained / 8);

            if ($remainder > 0 && mt_rand(1, 8) <= $remainder)
                $gain++;

            $pet->increaseSafety($gain);
            $pet->increaseLove($gain);
        }


        return $this->logActivity($pet, 'You fed ' . $pet->getName() . ' ' . array_list($foodsEaten) . '.', $petChanges->compare($pet));
    }

    /**
     * @return PetActivityLog[]
     */
    public function runHour(Pet $pet)
    {
        $activityLogs = [];

        if($pet->getTime() < 60)
            throw new \InvalidArgumentException('Pet does not have enough Time.');

        $pet->increaseFood(-1);

        if($pet->getJunk() > 0)
            $pet->increaseJunk(-1);

        if($pet->getWhack() > 0)
            $pet->increaseWhack(-1);

        if($pet->getSafety() > 0)
            $pet->increaseSafety(-1);
        else if($pet->getSafety() < 0)
            $pet->increaseSafety(1);

        if($pet->getLove() > 0)
            $pet->increaseLove(-1);

        if($pet->getEsteem() > 0)
            $pet->increaseEsteem(-1);

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
                $changes = new PetChanges($pet);

                // TODO: throw up

                $activityLogs[] = $this->logActivity($pet, $pet->getName() . ' threw up :(', $changes->compare($pet));

                return $activityLogs;
            }

            if($this->randomService->roll(1, 12) < $pet->getWhack())
            {
                // TODO: something whacky?
                return $activityLogs;
            }

            if($this->randomService->roll(1, $junkDie) < $pet->getJunk())
            {
                $activityLogs[] = $this->logActivity($pet, $pet->getName() . ' couldn\'t muster the energy to do anything.');

                return $activityLogs;
            }
        }

        // TODO: pick a productive activity

        return $activityLogs;
    }

    public function calculateAgeInDays(Pet $pet)
    {
        return (new \DateTimeImmutable())->diff($pet->getBirthDate())->days;
    }

    public function logActivity(Pet $pet, string $entry, ?PetChangesSummary $changes = null): PetActivityLog
    {
        $log = (new PetActivityLog())
            ->setPet($pet)
            ->setEntry($entry)
            ->setChanges($changes)
        ;

        $this->em->persist($log);

        return $log;
    }
}