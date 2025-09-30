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

namespace App\Service\PetActivity\Crafting;

use App\Entity\PetActivityLog;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\CalendarFunctions;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Model\ActivityCallback;
use App\Model\ComputedPetSkills;
use App\Model\HouseSimRecipe;
use App\Model\IActivityCallback;
use App\Service\Clock;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class EventLanternService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly PetExperienceService $petExperienceService,
        private readonly EntityManagerInterface $em,
        private readonly IRandom $rng,
        private readonly Clock $clock,
        private readonly HouseSimService $houseSimService
    )
    {
    }

    /**
     * @return IActivityCallback[]
     */
    public function getCraftingPossibilities(ComputedPetSkills $petWithSkills): array
    {
        $now = new \DateTimeImmutable();
        $possibilities = [];

        $recipe = new HouseSimRecipe([
            ItemRepository::findOneByName($this->em, 'Crooked Fishing Rod'),
            ItemRepository::findOneByName($this->em, 'Paper'),
            [ ItemRepository::findOneByName($this->em, 'Candle'), ItemRepository::findOneByName($this->em, 'Jar of Fireflies') ]
        ]);

        $items = $this->houseSimService->getState()->hasInventory($recipe);

        if($items)
        {
            if(CalendarFunctions::isHalloweenCrafting($this->clock->now))
                $possibilities[] = new ActivityCallback($this->createMoonlightLantern(...), 10);

            if(CalendarFunctions::isPiDayCrafting($this->clock->now))
                $possibilities[] = new ActivityCallback($this->createPiLantern(...), 10);

            if((int)$now->format('n') === 12)
                $possibilities[] = new ActivityCallback($this->createTreelightLantern(...), 10);

            if(CalendarFunctions::isSaintMartinsDayCrafting($this->clock->now))
                $possibilities[] = new ActivityCallback($this->createDapperSwanLantern(...), 10);
        }

        return $possibilities;
    }

    public function createMoonlightLantern(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->createLantern($petWithSkills, 'Moonlight Lantern', 'Halloween');
    }

    public function createPiLantern(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->createLantern($petWithSkills, 'Pi Lantern', 'Pi Day');
    }

    public function createTreeLightLantern(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->createLantern($petWithSkills, 'Treelight Lantern', 'Stocking Stuffing Season');
    }

    public function createDapperSwanLantern(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->createLantern($petWithSkills, 'Dapper Swan Lantern', 'St. Martin\'s');
    }

    private function createLantern(ComputedPetSkills $petWithSkills, string $lanternName, string $activityTag): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll < 15)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a seasonal lantern, but couldn\'t come up with a fitting design...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Crafting', 'Special Event', $activityTag ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);

        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Crooked Fishing Rod', 1);
            $this->houseSimService->getState()->loseItem('Paper', 1);
            $this->houseSimService->getState()->loseOneOf($this->rng, [ 'Jar of Fireflies', 'Candle' ]);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a ' . $lanternName . ' out of a Crooked Fishing Rod!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Crafting', 'Special Event', $activityTag ]))
            ;

            $this->inventoryService->petCollectsItem($lanternName, $pet, $pet->getName() . ' created this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
        }

        return $activityLog;
    }
}
