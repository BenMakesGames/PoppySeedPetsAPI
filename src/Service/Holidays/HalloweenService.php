<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Service\Holidays;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Entity\UserQuest;
use App\Enum\LocationEnum;
use App\Functions\UserQuestRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use Doctrine\ORM\EntityManagerInterface;

class HalloweenService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly EntityManagerInterface $em,
        private readonly IRandom $rng
    )
    {
    }

    public function getNextTrickOrTreater(User $user): UserQuest
    {
        return UserQuestRepository::findOrCreate($this->em, $user, 'Next Trick-or-Treater', (new \DateTimeImmutable())->modify('-1 day')->format('Y-m-d H:i:s'));
    }

    public function getTrickOrTreater(User $user): ?Pet
    {
        $trickOrTreater = UserQuestRepository::findOrCreate($this->em, $user, 'Trick-or-Treater', 0);

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
            return null;
        }

        $offset = $this->rng->rngNextInt(0, $numberOfPets - 1);

        return $this->em->getRepository(Pet::class)->createQueryBuilder('p')
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
    }

    public function resetTrickOrTreater(User $user): void
    {
        UserQuestRepository::findOrCreate($this->em, $user, 'Trick-or-Treater', 0)
            ->setValue(0)
        ;

        UserQuestRepository::findOrCreate($this->em, $user, 'Next Trick-or-Treater', '')
            ->setValue((new \DateTimeImmutable())->modify('+15 minutes')->format('Y-m-d H:i:s'))
        ;
    }

    public function countCandyGiven(User $user, Pet $trickOrTreater, bool $toGivingTree): ?Inventory
    {
        $treated = UserQuestRepository::findOrCreate($this->em, $user, 'Trick-or-Treaters Treated', 0);

        $treated->setValue($treated->getValue() + 1);

        $item = match($treated->getValue() % 61)
        {
            1 => 'Crooked Stick',
            3 => 'Super-wrinkled Cloth',
            8 => 'Smallish Pumpkin',
            15 => 'Glowing Six-sided Die',
            25 => 'Behatting Scroll',
            40 => 'Blood Wine',
            60 => 'Witch\'s Hat',
            default => null,
        };

        if($item)
        {
            if($toGivingTree)
                return $this->inventoryService->receiveItem($item, $user, $user, $user->getName() . ' found this at the Giving Tree during Halloween!', LocationEnum::Home);
            else
                return $this->inventoryService->receiveItem($item, $user, $trickOrTreater->getOwner(), $trickOrTreater->getName() . ' gave you this item after trick-or-treating. (Treats for everyone, I guess!)', LocationEnum::Home);
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
            ->setParameter('home', LocationEnum::Home)
            ->getQuery()
            ->execute()
        ;
    }
}
