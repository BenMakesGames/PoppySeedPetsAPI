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


namespace App\Service;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Functions\ItemRepository;
use App\Model\HouseSim;
use App\Model\HouseSimRecipe;
use App\Model\IHouseSim;
use App\Model\ItemQuantity;
use App\Model\NoHouseSim;
use Doctrine\ORM\EntityManagerInterface;

class HouseSimService
{
    // data
    private IHouseSim $houseState;
    private array $petIdsThatRanSocialTime = [];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PerformanceProfiler $performanceProfiler
    )
    {
        $this->houseState = new NoHouseSim();
    }

    public function begin(EntityManagerInterface $em, User $user): void
    {
        $time = microtime(true);

        $inventory = $em->getRepository(Inventory::class)->findBy([
            'owner' => $user,
            'location' => LocationEnum::HOME
        ]);

        $this->performanceProfiler->logExecutionTime(__METHOD__ . ' - Fetch Inventory', microtime(true) - $time);

        $this->houseState = new HouseSim($inventory);
        $this->petIdsThatRanSocialTime = [];
    }

    public function end(EntityManagerInterface $em): void
    {
        $toRemove = $this->houseState->getInventoryToRemove();
        $toPersist = $this->houseState->getInventoryToPersist();

        foreach($toRemove as $i)
            $em->remove($i);

        foreach($toPersist as $i)
            $em->persist($i);

        $this->houseState = new NoHouseSim();
    }

    public function getState(): IHouseSim
    {
        return $this->houseState;
    }

    public function hasInventory(string $itemName, int $quantity = 1): bool
    {
        if($quantity === 1)
        {
            $ingredient = ItemRepository::findOneByName($this->em, $itemName);
        }
        else
        {
            $ingredient = new ItemQuantity(
                ItemRepository::findOneByName($this->em, $itemName),
                $quantity
            );
        }

        return $this->getState()->hasInventory(new HouseSimRecipe([ $ingredient ]));
    }

    public function setPetHasRunSocialTime(Pet $pet): void
    {
        $this->petIdsThatRanSocialTime[] = $pet->getId();
    }

    public function getPetHasRunSocialTime(Pet $pet): bool
    {
        return in_array($pet->getId(), $this->petIdsThatRanSocialTime);
    }
}