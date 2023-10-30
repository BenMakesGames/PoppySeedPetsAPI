<?php
namespace App\Service\Holidays;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Entity\UserQuest;
use App\Enum\LocationEnum;
use App\Repository\InventoryRepository;
use App\Repository\PetRepository;
use App\Repository\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PerformanceProfiler;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;

class HalloweenService
{
    private UserQuestRepository $userQuestRepository;
    private InventoryService $inventoryService;
    private EntityManagerInterface $em;
    private IRandom $rng;
    private PerformanceProfiler $performanceProfiler;

    public function __construct(
        UserQuestRepository $userQuestRepository, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng, PerformanceProfiler $performanceProfiler
    )
    {
        $this->userQuestRepository = $userQuestRepository;
        $this->inventoryService = $inventoryService;
        $this->em = $em;
        $this->rng = $rng;
        $this->performanceProfiler = $performanceProfiler;
    }

    public function getNextTrickOrTreater(User $user): UserQuest
    {
        return $this->userQuestRepository->findOrCreate($user, 'Next Trick-or-Treater', (new \DateTimeImmutable())->modify('-1 day')->format('Y-m-d H:i:s'));
    }

    public function getTrickOrTreater(User $user): ?Pet
    {
        $trickOrTreater = $this->userQuestRepository->findOrCreate($user, 'Trick-or-Treater', 0);

        $pet = $trickOrTreater->getValue() === 0 ? null : $this->em->getRepository(Pet::class)->find($trickOrTreater->getValue());

        if($pet === null || $pet->getTool() === null || $pet->getHat() === null || $pet->getOwner()->getId() === $user->getId())
        {
            $pet = $this->findRandomTrickOrTreater($user);
            $trickOrTreater->setValue($pet ? $pet->getId() : 0);
        }

        return $pet;
    }

    public function findRandomTrickOrTreater(User $user): ?Pet
    {
        $time = microtime(true);

        $oneDayAgo = (new \DateTimeImmutable())->modify('-24 hours');

        $numberOfPets = (int)$this->em->getRepository(Pet::class)->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.tool IS NOT NULL')
            ->andWhere('p.hat IS NOT NULL')
            ->andWhere('p.lastInteracted >= :oneDayAgo')
            ->andWhere('p.owner != :user')
            ->setParameter('oneDayAgo', $oneDayAgo)
            ->setParameter('user', $user->getId())
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if($numberOfPets === 0)
        {
            $this->performanceProfiler->logExecutionTime(__METHOD__ . ' - 0 pets', microtime(true) - $time);
            return null;
        }

        $offset = $this->rng->rngNextInt(0, $numberOfPets - 1);

        $pet = $this->em->getRepository(Pet::class)->createQueryBuilder('p')
            ->andWhere('p.tool IS NOT NULL')
            ->andWhere('p.hat IS NOT NULL')
            ->andWhere('p.lastInteracted >= :oneDayAgo')
            ->andWhere('p.owner != :user')
            ->setParameter('oneDayAgo', $oneDayAgo)
            ->setParameter('user', $user->getId())
            ->setFirstResult($offset)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult()
        ;

        $this->performanceProfiler->logExecutionTime(__METHOD__ . ' - non-0 pets', microtime(true) - $time);

        return $pet;
    }

    public function resetTrickOrTreater(User $user)
    {
        $this->userQuestRepository->findOrCreate($user, 'Trick-or-Treater', 0)
            ->setValue(0)
        ;

        $this->userQuestRepository->findOrCreate($user, 'Next Trick-or-Treater', '')
            ->setValue((new \DateTimeImmutable())->modify('+15 minutes')->format('Y-m-d H:i:s'))
        ;
    }

    public function countCandyGiven(User $user, Pet $trickOrTreater, bool $toGivingTree): ?Inventory
    {
        $treated = $this->userQuestRepository->findOrCreate($user, 'Trick-or-Treaters Treated', 0);

        $treated->setValue($treated->getValue() + 1);

        $item = null;

        switch($treated->getValue() % 61)
        {
            case 1: $item = 'Crooked Stick'; break;
            case 3: $item = 'Super-wrinkled Cloth'; break;
            case 8: $item = 'Smallish Pumpkin'; break;
            case 15: $item = 'Glowing Six-sided Die'; break;
            case 25: $item = 'Behatting Scroll'; break;
            case 40: $item = 'Blood Wine'; break;
            case 60: $item = 'Witch\'s Hat'; break;
        }

        if($item)
        {
            if($toGivingTree)
                return $this->inventoryService->receiveItem($item, $user, $user, $user->getName() . ' found this at the Giving Tree during Halloween!', LocationEnum::HOME);
            else
                return $this->inventoryService->receiveItem($item, $user, $trickOrTreater->getOwner(), $trickOrTreater->getName() . ' gave you this item after trick-or-treating. (Treats for everyone, I guess!)', LocationEnum::HOME);
        }
        else
            return null;
    }

    /**
     * @return Inventory[]
     */
    public function getCandy(User $user): array
    {
        return $this->em->getRepository(Inventory::class)->createQueryBuilder('i')
            ->andWhere('i.owner = :user')
            ->andWhere('i.location = :home')
            ->leftJoin('i.item', 'item')
            ->leftJoin('item.food', 'food')
            ->andWhere('food.isCandy=1')
            ->setParameter('user', $user->getId())
            ->setParameter('home', LocationEnum::HOME)
            ->getQuery()
            ->execute()
        ;
    }
}
