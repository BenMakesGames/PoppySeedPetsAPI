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

namespace App\Service\PetActivity\SpecialLocations;

use App\Entity\PetActivityLog;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ActivityHelpers;
use App\Functions\AdventureMath;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class IsekaiAdventureService
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

        $activityLog = $this->doEncounter($petWithSkills);

        if($this->rng->rngNextBool())
        {
            $brokenKey = ItemRepository::findOneByName($this->em, 'Broken Hazard Key');
            $pet->getTool()->changeItem($brokenKey);

            $activityLog->appendEntry('Unfortunately, after returning home, the Hazard Key broke.');
        }

        $activityLog
            ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Adventure ]))
            ->setChanges($changes->compare($pet))
        ;

        if(AdventureMath::petAttractsBug($this->rng, $pet, 75))
            $this->inventoryService->petAttractsRandomBug($pet);

        return $activityLog;
    }

    private function doEncounter(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return match($this->rng->rngNextInt(1, 3)) {
            1 => $this->encounterBugArmyPrincess($petWithSkills),
            2 => $this->encounterMadInventor($petWithSkills),
            3 => $this->encounterCelestialWarriors($petWithSkills),
        };
    }

    // El-Hazard
    private function encounterBugArmyPrincess(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $petName = ActivityHelpers::PetName($pet);

        $roll = $this->rng->rngNextInt(1, 20) + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl()->getTotal();

        if($roll >= 15)
        {
            $pet->increaseEsteem(4);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet,
                $petName . ' got isekai\'d to a strange world, where they had to pretend to be a local princess! A high school student leading an army of bugs attacked, but ' . $petName . ' fought them off before being sent back home!')
                ->setIcon('items/key/hazard')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Fighting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Brawl ], $activityLog);

            foreach([ 'Antenna', 'Scales', 'White Cloth', 'Fiberglass' ] as $lootName)
                $this->inventoryService->petCollectsItem($lootName, $pet, $pet->getName() . ' got this from an isekai adventure as a "princess".', $activityLog);
        }
        else
        {
            $pet->increaseSafety(-2);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet,
                $petName . ' got isekai\'d to a strange world, where they had to pretend to be a local princess! A high school student leading an army of bugs attacked, and ' . $petName . ' was overwhelmed! They grabbed what they could before being sent back home!')
                ->setIcon('items/key/hazard')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Fighting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Brawl ], $activityLog);

            foreach([ 'Antenna', 'Filthy Cloth' ] as $lootName)
                $this->inventoryService->petCollectsItem($lootName, $pet, $pet->getName() . ' grabbed this while fleeing an isekai adventure.', $activityLog);
        }

        return $activityLog;
    }

    // Escaflowne
    private function encounterMadInventor(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $petName = ActivityHelpers::PetName($pet);

        $roll = $this->rng->rngNextInt(1, 20) + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getArcana()->getTotal();

        if($roll >= 15)
        {
            $pet->increaseEsteem(4);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, true);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet,
                $petName . ' got isekai\'d and discovered they had psychic powers! With the help of two warriors - who were BOTH flirting with ' . $petName . ' the entire time - they defeated a mad inventor before being sent back home!')
                ->setIcon('items/key/hazard')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Fighting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);

            foreach([ 'Glass Pendulum', 'Iron Bar', 'Lightning in a Bottle' ] as $lootName)
                $this->inventoryService->petCollectsItem($lootName, $pet, $pet->getName() . ' got this after defeating a mad inventor in an isekai adventure.', $activityLog);
        }
        else
        {
            $pet->increaseSafety(-2);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, false);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet,
                $petName . ' got isekai\'d and discovered they had psychic powers! Two warriors - who were BOTH flirting with ' . $petName . ' - tried to help fight a mad inventor, but ' . $petName . ' couldn\'t channel their powers in time, and were sent back home, defeated...')
                ->setIcon('items/key/hazard')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Fighting ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);

            $this->inventoryService->petCollectsItem('Glass Pendulum', $pet, $pet->getName() . ' received this while attempting to fight a mad inventor in an isekai adventure.', $activityLog);
        }

        return $activityLog;
    }

    // Fushigi Yûgi
    private function encounterCelestialWarriors(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $petName = ActivityHelpers::PetName($pet);

        $roll = $this->rng->rngNextInt(1, 20) + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getNature()->getTotal() + ($pet->getExtroverted() * 4);
        $recruited = min(7, max(1, (int)floor($roll / 4)));

        if($recruited >= 7)
        {
            $pet->increaseEsteem(4);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet,
                $petName . ' got isekai\'d as the destined priestess of a local god! ' . $petName . ' sought out all seven of the god\'s celestial warriors (some of whom ' . $petName . ' found VERY attractive) and recruited every last one! The god was so grateful, they sent ' . $petName . ' home with gifts.')
                ->setIcon('items/key/hazard')
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Nature ], $activityLog);

            $this->inventoryService->petCollectsItem('Moth', $pet, 'This followed ' . $pet->getName() . ' home after an isekai adventure of celestial warrior recruitment.', $activityLog);
            $this->inventoryService->petCollectsItem('Rapier', $pet, $pet->getName() . ' received this from a grateful god after recruiting all seven of their celestial warriors during an isekai adventure.', $activityLog);
            $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' received this from a grateful god after recruiting all seven of their celestial warriors during an isekai adventure.', $activityLog);
            $this->inventoryService->petCollectsItem('Quintessence', $pet, $pet->getName() . ' received this from a grateful god after recruiting all seven of their celestial warriors during an isekai adventure.', $activityLog);
        }
        else
        {
            $pet->increaseSafety(-2);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, false);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet,
                $petName . ' got isekai\'d as the destined priestess of a local god! ' . $petName . ' tried to recruit the god\'s celestial warriors (some of whom ' . $petName . ' found VERY attractive), but only managed to find ' . $recruited . ' before being sent back home.')
                ->setIcon('items/key/hazard')
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Nature ], $activityLog);

            $this->inventoryService->petCollectsItem('Moth', $pet, 'This followed ' . $pet->getName() . ' home after an isekai adventure of celestial warrior recruitment.', $activityLog);
            $this->inventoryService->petCollectsItem('Rusty Rapier', $pet, $pet->getName() . ' grabbed this during a failed isekai adventure of celestial warrior recruitment. (It HAD been in decent shape - the trip home must have damaged it.)', $activityLog);
        }

        return $activityLog;
    }
}
