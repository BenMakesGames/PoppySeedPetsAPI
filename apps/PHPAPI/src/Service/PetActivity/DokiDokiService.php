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


namespace App\Service\PetActivity;

use App\Entity\PetActivityLog;
use App\Entity\PetBadge;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetBadgeEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ActivityHelpers;
use App\Functions\AdventureMath;
use App\Functions\ArrayFunctions;
use App\Functions\EquipmentFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class DokiDokiService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly IRandom $rng,
        private readonly InventoryService $inventoryService,
        private readonly PetExperienceService $petExperienceService
    )
    {
    }

    public function adventure(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        EquipmentFunctions::destroyPetTool($this->em, $pet);

        $changes = new PetChanges($pet);

        $activityLog = match($this->rng->rngNextInt(1, 10))
        {
            1 => $this->hit1Enemy($petWithSkills),
            2, 3, 4, 5, 6 => $this->hitManyEnemies($petWithSkills),
            7, 8, 9, 10 => $this->defeatWeirdBird($petWithSkills),
        };

        $activityLog
            ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Adventure!' ]))
            ->setChanges($changes->compare($pet))
        ;

        if(AdventureMath::petAttractsBug($this->rng, $pet, 50))
            $this->inventoryService->petAttractsRandomBug($pet);

        return $activityLog;
    }

    private function hit1Enemy(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' went out and threw their Giant Radish at some enemies, but only managed to hit one. The radish was lost in the process... and with nothing to show for it!');

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(50, 60), PetActivityStatEnum::HUNT, false);

        return $activityLog;
    }

    private function hitManyEnemies(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $numEnemies = $this->rng->rngNextFromArray([
            'two', 'two', 'two', 'two',
            'three', 'three', 'three',
            'four', 'four',
            'five',
        ]);

        $loot = $this->rng->rngNextSubsetFromArray([
            'Goodberries',
            'Toadstool',
            'Stardust',
            'Music Note',
            $this->rng->rngNextFromArray([
                'Gold Key',
                'Gold Key',
                'Potion of Arcana',
                'Silica Grounds',
            ]),
        ], 2);

        if($numEnemies === 'five')
            $loot[] = 'Hourglass';
        else if($numEnemies === 'four')
            $loot[] = $this->rng->rngNextFromArray([ 'Hourglass', 'Goodberries' ]);

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' went out and threw their Giant Radish at some enemies, hitting ' . $numEnemies . ' in a row! The radish was lost in the process... but they were rewarded with ' . ArrayFunctions::list_nice_sorted($loot) . '!');

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(50, 60), PetActivityStatEnum::HUNT, true);

        foreach($loot as $item)
            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' received for tossing a Giant Radish at some enemies.', $activityLog);

        return $activityLog;
    }

    private function defeatWeirdBird(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' went out and threw their Giant Radish at a weird bird monster, defeating it! The radish was lost in the process... but they were rewarded with a couple Eggs, and a Crystal Ball!');

        $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(50, 60), PetActivityStatEnum::HUNT, true);

        $items = [
            'Egg', 'Egg', 'Crystal Ball'
        ];

        foreach($items as $item)
            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' received for defeating a weird bird monster with a Giant Radish.', $activityLog);

        return $activityLog;
    }
}