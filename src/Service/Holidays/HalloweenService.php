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
use App\Service\CalendarService;
use App\Service\InventoryService;

class HalloweenService
{
    private $userQuestRepository;
    private $petRepository;
    private $inventoryService;
    private $inventoryRepository;

    public function __construct(
        UserQuestRepository $userQuestRepository, PetRepository $petRepository, InventoryService $inventoryService,
        InventoryRepository $inventoryRepository, CalendarService $calendarService
    )
    {
        $this->userQuestRepository = $userQuestRepository;
        $this->petRepository = $petRepository;
        $this->inventoryService = $inventoryService;
        $this->inventoryRepository = $inventoryRepository;
    }

    public function getNextTrickOrTreater(User $user): UserQuest
    {
        return $this->userQuestRepository->findOrCreate($user, 'Next Trick-or-Treater', (new \DateTimeImmutable())->modify('-1 day')->format('Y-m-d H:i:s'));
    }

    public function getTrickOrTreater(User $user): ?Pet
    {
        $trickOrTreater = $this->userQuestRepository->findOrCreate($user, 'Trick-or-Treater', 0);

        $pet = $trickOrTreater->getValue() === 0 ? null : $this->petRepository->find($trickOrTreater->getValue());

        if($pet === null || $pet->getTool() === null || $pet->getHat() === null || $pet->getOwner()->getId() === $user->getId())
        {
            $pet = $this->petRepository->findRandomTrickOrTreater($user);
            $trickOrTreater->setValue($pet ? $pet->getId() : 0);
        }

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
            case 3: $item = 'Glowing Four-sided Die'; break;
            case 8: $item = 'Smallish Pumpkin'; break;
            case 15: $item = 'Glowing Six-sided Die'; break;
            case 25: $item = 'Behatting Scroll'; break;
            case 40: $item = 'Glowing Eight-sided Die'; break;
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
        return $this->inventoryRepository->createQueryBuilder('i')
            ->andWhere('i.owner = :user')
            ->andWhere('i.location = :home')
            ->leftJoin('i.item', 'item')
            ->leftJoin('item.food', 'food')
            ->andWhere('food.love > food.food - food.junk / 2')
            ->setParameter('user', $user->getId())
            ->setParameter('home', LocationEnum::HOME)
            ->getQuery()
            ->execute()
        ;
    }
}
