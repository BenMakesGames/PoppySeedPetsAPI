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
use App\Exceptions\UnreachableException;
use App\Functions\ActivityHelpers;
use App\Functions\AdventureMath;
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

class JumpRopeService
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

        $changes = new PetChanges($pet);

        $activityLog = match($this->rng->rngNextInt(1, 10))
        {
            1 => $this->basicBounce($petWithSkills),
            2 => $this->crissCross($petWithSkills),
            3 => $this->doubleUnder($petWithSkills),
            4 => $this->scissorJump($petWithSkills),
            5 => $this->toad($petWithSkills),
            6 => $this->ropeRelease($petWithSkills),
            7 => $this->heelToToe($petWithSkills),
            8 => $this->mobiusFlip($petWithSkills),
            9 => $this->shadowWeave($petWithSkills),
            10 => $this->schrodingerSkip($petWithSkills),
            default => throw new UnreachableException()
        };

        $activityLog
            ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Adventure!' ]))
            ->setChanges($pet, $changes->compare($pet))
        ;

        $bugChance = $pet->getBadges()->exists(Fn(int $i, PetBadge $p) => $p->getBadge() === PetBadgeEnum::JUMPED_ROPE_WITH_A_BUG)
            ? 75
            : 6;

        if(AdventureMath::petAttractsBug($this->rng, $pet, $bugChance))
        {
            if($this->inventoryService->petAttractsRandomBug($pet))
                PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::JUMPED_ROPE_WITH_A_BUG, $activityLog);
        }

        return $activityLog;
    }

    private function doBreakCheck(ComputedPetSkills $petWithSkills, int $difficulty, PetActivityLog $log): void
    {
        $skillCheck = $this->rng->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStamina()->getTotal());

        if($skillCheck < $difficulty)
        {
            $log->setEntry($log->getEntry() . ' Unfortunately, the Jump Rope couldn\'t handle the stress, and fell apart!');

            $pet = $petWithSkills->getPet();
            EquipmentFunctions::destroyPetTool($this->em, $pet);
        }
    }

    private function basicBounce(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $location = $this->rng->rngNextFromArray([
            PetActivityLogTagEnum::Location_Neighborhood,
            PetActivityLogTagEnum::Location_At_Home
        ]);

        $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' performed some Basic Bounces with their Jump Rope.')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                PetActivityLogTagEnum::Adventure,
                $location
            ]));

        $this->doBreakCheck($petWithSkills, 5, $log);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $log);

        return $log;
    }

    private function crissCross(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $location = $this->rng->rngNextFromArray([
            PetActivityLogTagEnum::Location_Neighborhood,
            PetActivityLogTagEnum::Location_At_Home
        ]);

        $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' performed some Criss-crosses with their Jump Rope.')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                PetActivityLogTagEnum::Adventure,
                $location
            ]));

        $this->doBreakCheck($petWithSkills, 7, $log);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $log);

        return $log;
    }

    private function doubleUnder(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $location = $this->rng->rngNextFromArray([
            PetActivityLogTagEnum::Location_Neighborhood,
            PetActivityLogTagEnum::Location_At_Home
        ]);

        $mineral = $this->rng->rngNextFromArray([
            'Silica Grounds',
            'Iron Ore',
            'Silver Ore',
            'Gold Ore',
            'Gypsum',
        ]);

        $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' performed some Double-unders with their Jump Rope. The strength of their jump rattled some nearby rocks, which crumbled, revealing some ' . $mineral . '.')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                PetActivityLogTagEnum::Adventure,
                $location
            ]));

        $this->inventoryService->petCollectsItem($mineral, $pet, 'Fell out a rock that was shaken by ' . $pet->getName() . '\'s powerful jump rope skills.', $log);

        $this->doBreakCheck($petWithSkills, 9, $log);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $log);

        return $log;
    }

    private function scissorJump(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $location = $this->rng->rngNextFromArray([
            PetActivityLogTagEnum::Location_Neighborhood,
            PetActivityLogTagEnum::Location_At_Home
        ]);

        $animal = $this->rng->rngNextFromArray([
            'mongoose',
            'loris (the non-slow kind)',
            'anteater',
            'tree shrew',
            'mouse deer',
            'rat',
        ]);

        $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' performed some Scissor Jumps with their Jump Rope. Their jumps were so sudden and so sharp, they startled a passing ' . $animal . ', which, in its confusion, ran close to ' . ActivityHelpers::PetName($pet) . ' and got some of its Fluff trimmed off!')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                PetActivityLogTagEnum::Adventure,
                $location
            ]));

        $this->inventoryService->petCollectsItem('Fluff', $pet, 'Trimmed off a passing ' . $animal . ' by ' . $pet->getName() . '\'s sharp jump rope skills.', $log);
        $this->inventoryService->petCollectsItem('Fluff', $pet, 'Trimmed off a passing ' . $animal . ' by ' . $pet->getName() . '\'s sharp jump rope skills.', $log);

        $this->doBreakCheck($petWithSkills, 13, $log);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL, PetSkillEnum::CRAFTS ], $log);

        return $log;
    }

    private function toad(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $location = $this->rng->rngNextFromArray([
            PetActivityLogTagEnum::Location_Neighborhood,
            PetActivityLogTagEnum::Location_Stream,
            PetActivityLogTagEnum::Location_Hollow_Log,
            PetActivityLogTagEnum::Location_Small_Lake,
        ]);

        $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' performed some Toads with their Jump Rope - you know, where you cross your legs mid-air while doing a basic jump? An actual toad happened to be watching, and was so impressed it forgot its legs when it finally hopped away.')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                PetActivityLogTagEnum::Adventure,
                $location
            ]));

        $this->inventoryService->petCollectsItem('Toad Legs', $pet, 'Dropped by a toad who was so impressed with ' . $pet->getName() . '\'s jump rope skills.', $log);

        $this->doBreakCheck($petWithSkills, 15, $log);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);
        $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ], $log);

        return $log;
    }

    private function ropeRelease(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $location = $this->rng->rngNextFromArray([
            PetActivityLogTagEnum::Location_Micro_Jungle,
        ]);

        $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' performed some Rope Releases with their Jump Rope - you know, where you let go of both handles while mid-jump, then catch them again? Except this one time, they caught some falling tree fruit, instead!')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                PetActivityLogTagEnum::Adventure,
                $location
            ]));

        $fruit = [
            'Naner',
            'Cacao Fruit',
            'Mango',
            'Red',
            'Orange',
            'Pamplemousse',
            'Yellowy Lime',
        ];

        shuffle($fruit);

        $this->inventoryService->petCollectsItem($fruit[0], $pet, 'Fell out of a tree while ' . $pet->getName() . '\'s was practicing their jump rope skills.', $log);
        $this->inventoryService->petCollectsItem($fruit[1], $pet, 'Fell out of a tree while ' . $pet->getName() . '\'s was practicing their jump rope skills.', $log);

        $this->doBreakCheck($petWithSkills, 17, $log);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);
        $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL, PetSkillEnum::SCIENCE ], $log);

        return $log;
    }

    private function heelToToe(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $location = $this->rng->rngNextFromArray([
            PetActivityLogTagEnum::Location_Roadside_Creek,
            PetActivityLogTagEnum::Location_Stream,
            PetActivityLogTagEnum::Location_Small_Lake,
        ]);

        $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' performed some Heel-to-Toes with their Jump Rope - you know, where you alternate between landing on your heel and toe in a fluid motion? The motion was _so_ fluid, some fish became confused and leapt out of the water, landing near ' . ActivityHelpers::PetName($pet) . '.')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                PetActivityLogTagEnum::Adventure,
                $location
            ]));

        $this->inventoryService->petCollectsItem('Fish', $pet, 'Leapt out of the water due to ' . $pet->getName() . '\'s extremely fluid jump rope skills.', $log);
        $this->inventoryService->petCollectsItem('Fish', $pet, 'Leapt out of the water due to ' . $pet->getName() . '\'s extremely fluid jump rope skills.', $log);

        $this->doBreakCheck($petWithSkills, 20, $log);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);
        $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::BRAWL, PetSkillEnum::SCIENCE ], $log);

        return $log;
    }

    private function mobiusFlip(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $location = $this->rng->rngNextFromArray([
            PetActivityLogTagEnum::Location_Abandoned_Quarry,
            PetActivityLogTagEnum::Location_Neighborhood,
            PetActivityLogTagEnum::Location_Roadside_Creek,
        ]);

        $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' performed some Möbius Flips with their Jump Rope - you know, where you create a continuous loop with the rope while performing a backflip? The maneuver was so complex, it caused a hot dog to leap out of a nearby paper bag, and take on some complex properties of its own.')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                PetActivityLogTagEnum::Adventure,
                $location
            ]));

        $this->inventoryService->petCollectsItem('Mandelbrat', $pet, 'Produced by ' . $pet->getName() . '\'s complex jump rope skills.', $log);

        $this->doBreakCheck($petWithSkills, 23, $log);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);
        $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::BRAWL, PetSkillEnum::SCIENCE ], $log);

        return $log;
    }

    private function shadowWeave(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $location = $this->rng->rngNextFromArray([
            PetActivityLogTagEnum::Location_Neighborhood,
            PetActivityLogTagEnum::Location_Roadside_Creek
        ]);

        $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' performed some Shadow Weaves with their Jump Rope - you know, where intricate manipulations of the rope make it appear as if its passing straight through you? The illusion was so convincing, the universe felt compelled to create and kick up some dust to try and conceal the act.')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                PetActivityLogTagEnum::Adventure,
                $location
            ]));

        $this->inventoryService->petCollectsItem('Magic Smoke', $pet, 'Produced by the universe in response to ' . $pet->getName() . '\'s super-natural jump rope skills.', $log);

        $this->doBreakCheck($petWithSkills, 26, $log);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);
        $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::BRAWL, PetSkillEnum::STEALTH, PetSkillEnum::ARCANA ], $log);

        return $log;
    }

    private function schrodingerSkip(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $location = $this->rng->rngNextFromArray([
            PetActivityLogTagEnum::Location_Neighborhood,
            PetActivityLogTagEnum::Location_At_Home
        ]);

        $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' performed some Schrödinger\'s Skips with their Jump Rope - you know, where you jump and don\'t jump at the same time? The act perturbed the surrounding fabric of space-time, creating some Strange Fields...')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                PetActivityLogTagEnum::Adventure,
                $location
            ]));

        $this->inventoryService->petCollectsItem('Strange Field', $pet, 'Produced by ' . $pet->getName() . '\'s sci-fi jump rope skills.', $log);
        $this->inventoryService->petCollectsItem('Strange Field', $pet, 'Produced by ' . $pet->getName() . '\'s sci-fi jump rope skills.', $log);

        $this->doBreakCheck($petWithSkills, 30, $log);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);
        $this->petExperienceService->gainExp($pet, 5, [ PetSkillEnum::BRAWL, PetSkillEnum::SCIENCE ], $log);

        return $log;
    }
}