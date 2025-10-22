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
use App\Enum\DistractionLocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Model\ComputedPetSkills;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

final class WildHedgeMazeService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly IRandom $rng,
        private readonly PetExperienceService $petExperienceService,
        private readonly GatheringDistractionService $gatheringDistractions,
        private readonly InventoryService $inventoryService,
    )
    {
    }

    private function exploreHedgeMaze(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->rng->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::Woods, 'exploring the woods');

        $pet = $petWithSkills->getPet();

        $petHasEideticMemory = $pet->hasMerit(MeritEnum::EIDETIC_MEMORY);
        $avoidedGettingLost = false;

        if($this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal()) < 15)
        {
            if($petHasEideticMemory || $petWithSkills->getClimbingBonus()->getTotal() > 0)
                $avoidedGettingLost = true;
            else
                return $this->lostInHedgeMaze($petWithSkills);
        }

        $possibilities = [];

        // a pet with an eidetic memory and no mirror will remember that they shouldn't even attempt the light puzzle
        if(!(!$pet->getTool()?->getItem()->hasItemGroup('Mirror') && $petHasEideticMemory))
        {
            $possibilities[] = $this->lightPuzzle(...);
        }

        $activityLog = $this->rng->rngNextFromArray($possibilities)($petWithSkills);
    }

    private function lightPuzzle(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        // TODO
    }

    private function foundWildHedgemaze(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->rng->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::Woods, 'exploring the woods');

        $pet = $petWithSkills->getPet();

        $possibleLoot = [
            'Smallish Pumpkin', 'Crooked Stick', 'Sweet Beet', 'Toadstool', 'Grandparoot', 'Pamplemousse',
        ];

        if($this->rng->rngNextInt(1, 20) === 1)
        {
            $possibleLoot[] = $this->rng->rngNextFromArray([
                'Glowing Four-sided Die',
                'Glowing Six-sided Die',
                'Glowing Eight-sided Die'
            ]);
        }

        $loot = [];

        if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY) || $petWithSkills->getClimbingBonus()->getTotal() > 0)
        {
            $loot[] = $this->rng->rngNextFromArray($possibleLoot);
            $loot[] = $this->rng->rngNextFromArray($possibleLoot);

            if($this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 20)
                $loot[] = $this->rng->rngNextFromArray($possibleLoot);

            $lucky = false;

            if($pet->hasMerit(MeritEnum::LUCKY) && $this->rng->rngNextInt(1, 15) === 1)
            {
                $loot[] = 'Melowatern';
                $lucky = true;
            }
            else if($this->rng->rngNextInt(1, 75) == 1)
                $loot[] = 'Melowatern';

            if($petWithSkills->getClimbingBonus()->getTotal() > 0)
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% went to the Wild Hedgemaze. It turns out mazes are way easier when you can just climb over the walls! ' . $pet->getName() . ' found ' . ArrayFunctions::list_nice_sorted($loot) . '.');
            else
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% went to the Wild Hedgemaze. It turns out mazes are way easier with a perfect memory! ' . $pet->getName() . ' found ' . ArrayFunctions::list_nice_sorted($loot) . '.');

            $tags = [ 'Gathering' ];

            if($lucky)
            {
                $activityLog
                    ->appendEntry('(Melowatern!? Lucky~!)')
                    ->addInterestingness(PetActivityLogInterestingness::ActivityUsingMerit)
                ;
                $tags[] = 'Lucky~!';
            }

            $activityLog
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, $tags))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Nature ], $activityLog);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::GATHER, true);
        }
        else if($this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal()) < 15)
        {
            $pet->increaseFood(-1);

            if($this->rng->rngNextInt(1, 20) + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getArcana()->getTotal() >= 15)
            {
                $loot[] = $this->rng->rngNextFromArray($possibleLoot);
                $loot[] = $this->rng->rngNextFromArray($possibleLoot);

                if($this->rng->rngNextInt(1, 8) === 1)
                    $loot[] = 'Silver Ore';
                else if($this->rng->rngNextInt(1, 8) === 1)
                    $loot[] = 'Music Note';
                else
                    $loot[] = 'Quintessence';

                if($this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 25)
                    $loot[] = $this->rng->rngNextFromArray($possibleLoot);

                $pet->increaseEsteem($this->rng->rngNextInt(2, 3));
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% got lost in a Wild Hedgemaze, and ran into a Hedgemaze Sphinx. ' . $pet->getName() . ' was able to solve its riddle, and kept exploring, coming away with ' . ArrayFunctions::list_nice_sorted($loot) . '.')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]))
                ;

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana, PetSkillEnum::Nature ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::GATHER, true);
            }
            else
            {
                $pet->increaseEsteem(-$this->rng->rngNextInt(1, 2));
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% got lost in a Wild Hedgemaze, and ran into a Hedgemaze Sphinx. The sphinx asked a really hard question; ' . $pet->getName() . ' wasn\'t able to answer it, and was consequentially ejected from the maze.')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]))
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana, PetSkillEnum::Nature ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::GATHER, false);
            }
        }
        else
        {
            $loot[] = $this->rng->rngNextFromArray($possibleLoot);
            $loot[] = $this->rng->rngNextFromArray($possibleLoot);

            if($this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 25)
                $loot[] = $this->rng->rngNextFromArray($possibleLoot);

            $lucky = false;

            if($pet->hasMerit(MeritEnum::LUCKY) && $this->rng->rngNextInt(1, 20) === 1)
            {
                $loot[] = 'Melowatern';
                $lucky = true;
            }
            else if($this->rng->rngNextInt(1, 100) == 1)
                $loot[] = 'Melowatern';

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% wandered through a Wild Hedgemaze, and found ' . ArrayFunctions::list_nice_sorted($loot) . '.');

            $tags = [ 'Gathering' ];

            if($lucky)
            {
                $activityLog
                    ->appendEntry('(Melowatern!? Lucky~!)')
                    ->addInterestingness(PetActivityLogInterestingness::ActivityUsingMerit)
                ;
                $tags[] = 'Lucky~!';
            }

            $activityLog->addTags(PetActivityLogTagHelpers::findByNames($this->em, $tags));

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Nature ], $activityLog);

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
        }

        foreach($loot as $itemName)
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' found this in a Wild Hedgemaze.', $activityLog);

        return $activityLog;
    }


}