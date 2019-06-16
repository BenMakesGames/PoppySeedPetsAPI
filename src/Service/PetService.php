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

        if($pet->getFood() + $pet->getWhack() > 0)
            $pet->setSafety(min($pet->getMaxSafety(), $pet->getSafety() + 1));

        if($pet->getFood() + $pet->getWhack() > 0 && $pet->getSafety() + $pet->getWhack() > 0)
            $pet->setLove(min($pet->getMaxLove(), $pet->getLove() + 1));

        return $this->logActivity($pet, 'You pet ' . $pet->getName(). '.', $changes->compare($pet));
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

            if($food->junk) $pet->setJunk($pet->getJunk() + $food->junk);
            if($food->whack) $pet->setWhack($pet->getWhack() + $food->whack);
            if($food->food) $pet->setFood($pet->getFood() + $food->food);

            if($pet->getFood() + $pet->getWhack() - $pet->getJunk() > 0)
                if($food->love) $pet->setLove($pet->getLove() + $food->love);

            $this->em->remove($i);

            $foodsEaten[] = $i->getItem()->getName();

            if($pet->getJunk() + $pet->getWhack() + $pet->getFood() > 16)
                break;
        }

        return $this->logActivity($pet, $pet->getName() . ' ate ' . array_list($foodsEaten) . '.', $petChanges->compare($pet));
    }

    /**
     * @return PetActivityLog[]
     */
    public function runHour(Pet $pet)
    {
        $activityLogs = [];

        if($pet->getTime() < 60)
            throw new \InvalidArgumentException('Pet does not have enough Time.');

        $pet->setFood($pet->getFood() - 1);

        if($pet->getSafety() > 0)
            $pet->setSafety($pet->getSafety() - 1);
        else if($pet->getSafety() < 0 && $pet->getFood() + $pet->getWhack() > 0)
            $pet->setSafety($pet->getSafety() + 1);

        if($pet->getLove() > 0)
            $pet->setLove($pet->getLove() - 1);

        if($pet->getEsteem() > 0)
            $pet->setEsteem($pet->getEsteem() - 1);

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