<?php
namespace App\Service;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Entity\UserQuest;
use App\Enum\LocationEnum;
use App\Repository\PetRepository;
use App\Repository\UserQuestRepository;

class HalloweenService
{
    private $userQuestRepository;
    private $petRepository;
    private $inventoryService;

    public function __construct(
        UserQuestRepository $userQuestRepository, PetRepository $petRepository, InventoryService $inventoryService
    )
    {
        $this->userQuestRepository = $userQuestRepository;
        $this->petRepository = $petRepository;
        $this->inventoryService = $inventoryService;
    }

    public function isHalloween()
    {
        $monthAndDay = (int)(new \DateTimeImmutable())->format('md');

        return $monthAndDay >= 1029 && $monthAndDay <= 1031;
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

    public function countCandyGiven(User $user, Pet $trickOrTreater): ?Inventory
    {
        $treated = $this->userQuestRepository->findOrCreate($user, 'Trick-or-Treaters Treated', 0);

        $treated->setValue($treated->getValue() + 1);

        $item = null;

        switch($treated->getValue() % 61)
        {
            case 1: $item = 'Crooked Stick'; break;
            case 3: $item = ''; break;
            case 8: $item = ''; break;
            case 15: $item = ''; break;
            case 25: $item = ''; break;
            case 40: $item = ''; break;
            case 60: $item = ''; break;
        }

        if($item)
            return $this->inventoryService->receiveItem($item, $user, $trickOrTreater->getOwner(), $trickOrTreater->getName() . ' gave you this item after trick-or-treating. (Treats for everyone, I guess!)', LocationEnum::HOME);
        else
            return null;
    }
}