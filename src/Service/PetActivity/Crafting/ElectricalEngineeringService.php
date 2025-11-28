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
use App\Enum\ActivityPersonalityEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ActivityHelpers;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Model\ComputedPetSkills;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetActivity\IPetActivity;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class ElectricalEngineeringService implements IPetActivity
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly IRandom $rng, private readonly PetExperienceService $petExperienceService,
        private readonly HouseSimService $houseSimService, private readonly EntityManagerInterface $em
    )
    {
    }

    public function preferredWithFullHouse(): bool { return true; }

    public function groupKey(): string { return 'electricalEngineering'; }

    public function groupDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasStatusEffect(StatusEffectEnum::Wereform))
            return 0;

        $desire = $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getElectronicsBonus()->getTotal();

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getScience() + $pet->getTool()->getItem()->getTool()->getElectronics();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::CraftingScience))
            $desire += 4;
        else
            $desire += $this->rng->rngNextInt(1, 4);

        return max(1, (int)round($desire * (1 + $this->rng->rngNextInt(-10, 10) / 100)));
    }

    public function possibilities(ComputedPetSkills $petWithSkills): array
    {
        $possibilities = [];

        if($this->houseSimService->hasInventory('3D Printer') && $this->houseSimService->hasInventory('Plastic'))
        {
            if($this->houseSimService->hasInventory('Glass') && ($this->houseSimService->hasInventory('Silver Bar') || $this->houseSimService->hasInventory('Gold Bar')))
                $possibilities[] = $this->createLaserPointer(...);

            if(($this->houseSimService->hasInventory('Silver Bar') || $this->houseSimService->hasInventory('Iron Bar')) && $this->houseSimService->hasInventory('Magic Smoke'))
                $possibilities[] = $this->createMetalDetector(...);
        }

        if($this->houseSimService->hasInventory('Metal Detector (Iron)') || $this->houseSimService->hasInventory('Metal Detector (Silver)') || $this->houseSimService->hasInventory('Metal Detector (Gold)'))
        {
            if($this->houseSimService->hasInventory('Gold Bar') && ($this->houseSimService->hasInventory('Fiberglass') || $this->houseSimService->hasInventory('Fiberglass Flute')))
                $possibilities[] = $this->createSeashellDetector(...);
        }

        if($this->houseSimService->hasInventory('Hash Table'))
        {
            if($this->houseSimService->hasInventory('Laser Pointer') && $this->houseSimService->hasInventory('Bass Guitar'))
                $possibilities[] = $this->createLaserGuitar(...);

            if($this->houseSimService->hasInventory('XOR') && $this->houseSimService->hasInventory('Fiberglass Bow'))
                $possibilities[] = $this->createResonatingBow(...);

            if($this->houseSimService->hasInventory('Lightning Sword') && $this->houseSimService->hasInventory('Glass Pendulum'))
                $possibilities[] = $this->createRainbowsaber(...);
        }

        if($this->houseSimService->hasInventory('Lightning in a Bottle'))
        {
            if($this->houseSimService->hasInventory('Gold Bar'))
            {
                if($this->houseSimService->hasInventory('Plastic Boomerang'))
                    $possibilities[] = $this->createBuggerang(...);
            }
        }

        if($this->houseSimService->hasInventory('Magic Smoke'))
        {
            if($this->houseSimService->hasInventory('Laser Pointer') && $this->houseSimService->hasInventory('Toy Alien Gun'))
                $possibilities[] = $this->createAlienGun(...);

            if($this->houseSimService->hasInventory('Lightning Sword') && $this->houseSimService->hasInventory('Alien Tissue'))
                $possibilities[] = $this->createDNA(...);

            if($this->houseSimService->hasInventory('Gold Tuning Fork') && $this->houseSimService->hasInventory('Crooked Stick'))
                $possibilities[] = $this->createDIYTheremin(...);
        }

        if($this->houseSimService->hasInventory('Sylvan Fishing Rod') && $this->houseSimService->hasInventory('Laser Pointer') && $this->houseSimService->hasInventory('Alien Tissue'))
            $possibilities[] = $this->createAlienFishingRod(...);

        return $possibilities;
    }

    private function createLaserPointer(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngSkillRoll($petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getElectronicsBonus()->getTotal());

        $metalToUse = [ 'Silver Bar', 'Gold Bar' ];

        if($pet->hasMerit(MeritEnum::SILVERBLOOD) && $this->houseSimService->hasInventory('Silver Bar'))
        {
            $roll += 5;
            $metalToUse = [ 'Silver Bar' ];
        }

        if($roll <= 2)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Laser Pointer, but the 3D Printer started acting, and %pet:' . $pet->getId() . '.name% ended up spending all their time rechecking wires and software settings...')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ '3D Printing', 'Electronics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science, PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PLASTIC_PRINT, false);
        }
        else if($roll > 15)
        {
            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $this->houseSimService->getState()->loseItem('Glass', 1);

            $this->houseSimService->getState()->loseOneOf($this->rng, $metalToUse);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% 3D printed & wired a Laser Pointer.')
                ->setIcon('items/resource/string')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 15)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ '3D Printing', 'Electronics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Science, PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PLASTIC_PRINT, true);

            $this->inventoryService->petCollectsItem('Laser Pointer', $pet, $pet->getName() . ' 3D printed & wired this up.', $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Laser Pointer, but couldn\'t get the wiring straight.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ '3D Printing', 'Electronics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts, PetSkillEnum::Science ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PLASTIC_PRINT, false);
        }

        return $activityLog;
    }

    private function createMetalDetector(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngSkillRoll($petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getElectronicsBonus()->getTotal());

        $metalToUse = [ 'Silver Bar', 'Iron Bar' ];

        if($pet->hasMerit(MeritEnum::SILVERBLOOD) && $this->houseSimService->hasInventory('Silver Bar'))
        {
            $roll += 5;
            $metalToUse = [ 'Silver Bar' ];
        }

        if($roll <= 2)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Metal Detector, but the 3D Printer started acting, and %pet:' . $pet->getId() . '.name% ended up spending all their time rechecking wires and software settings...')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ '3D Printing', 'Electronics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science, PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PLASTIC_PRINT, false);
        }
        else if($roll > 15)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);

            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $this->houseSimService->getState()->loseItem('Magic Smoke', 1);

            $this->houseSimService->getState()->loseOneOf($this->rng, $metalToUse);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% 3D printed & wired up a Metal Detector.')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 16)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ '3D Printing', 'Electronics' ]))
            ;

            if($pet->hasMerit(MeritEnum::SILVERBLOOD))
            {
                $metalDetector = 'Metal Detector (Silver)';

                $this->inventoryService->petCollectsItem($metalDetector, $pet, $pet->getName() . ' 3D printed & wired this up, setting it to Silver, because that\'s the best metal. Obviously.', $activityLog);
            }
            else
            {
                $metalDetector = $this->rng->rngNextFromArray([
                    'Metal Detector (Iron)',
                    'Metal Detector (Silver)',
                    'Metal Detector (Gold)'
                ]);

                $this->inventoryService->petCollectsItem($metalDetector, $pet, $pet->getName() . ' 3D printed & wired this up.', $activityLog);
            }

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Science, PetSkillEnum::Crafts ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Metal Detector, but couldn\'t get the wiring straight.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ '3D Printing', 'Electronics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts, PetSkillEnum::Science ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
        }

        return $activityLog;
    }

    private function createSeashellDetector(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngSkillRoll($petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getElectronicsBonus()->getTotal());

        if($roll >= 18)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, false);

            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseOneOf($this->rng, [ 'Fiberglass', 'Fiberglass Flute' ]);
            $this->houseSimService->getState()->loseOneOf($this->rng, [ 'Metal Detector (Iron)', 'Metal Detector (Silver)', 'Metal Detector (Gold)' ]);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% modified an ordinary Metal Detector, turning it into a Secret Seashell Detector!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 18)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics' ]))
            ;
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Science ], $activityLog);
            $this->inventoryService->petCollectsItem('Secret Seashell Detector', $pet, $pet->getName() . ' made this out of an ordinary Metal Detector.', $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to alter a Metal Detector to detect Secret Seashells, but kept messing up the electronics.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Science ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createLaserGuitar(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngSkillRoll($petWithSkills->getIntelligence()->getTotal() + min($petWithSkills->getPerception()->getTotal(), $petWithSkills->getMusic()->getTotal()) + $petWithSkills->getScience()->getTotal() + $petWithSkills->getElectronicsBonus()->getTotal());

        if($roll >= 18)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Hash Table', 1);
            $this->houseSimService->getState()->loseItem('Laser Pointer', 1);
            $this->houseSimService->getState()->loseItem('Bass Guitar', 1);
            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Laser Guitar!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 18)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics' ]))
            ;
            $this->inventoryService->petCollectsItem('Laser Guitar', $pet, $pet->getName() . ' created this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Science, PetSkillEnum::Music ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% started to create Laser Guitar, but only got so far.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics' ]))
            ;

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science ], $activityLog);
        }

        return $activityLog;
    }

    private function createRainbowsaber(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngSkillRoll($petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getElectronicsBonus()->getTotal());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Glass Pendulum', 1);

            $pet->increaseEsteem(-3);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to put together a Rainbowsaber, but accidentally broke the Glass Pendulum they were trying to put inside; only its String remains :(')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 20)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics' ]))
            ;

            $this->inventoryService->petCollectsItem('String', $pet, $pet->getName() . ' accidentally broke a Glass Pendulum while trying to make a Rainbowsaber... this is all that remains.', $activityLog);

            return $activityLog;
        }
        else if($roll >= 20)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Hash Table', 1);
            $this->houseSimService->getState()->loseItem('Lightning Sword', 1);
            $this->houseSimService->getState()->loseItem('Glass Pendulum', 1);
            $pet->increaseEsteem(5);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Rainbowsaber!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 20)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics' ]))
            ;
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Science ], $activityLog);
            $this->inventoryService->petCollectsItem('Rainbowsaber', $pet, $pet->getName() . ' created this.', $activityLog);
        }
        else
        {
            $pet->increaseSafety(-1);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to put together a Rainbowsaber, but kept zapping themselves on the Lightning Sword! >:(')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Science ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
        }

        return $activityLog;
    }

    private function createResonatingBow(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngSkillRoll($petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getMusic()->getTotal() + $petWithSkills->getElectronicsBonus()->getTotal());

        if($roll >= 18)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Hash Table', 1);
            $this->houseSimService->getState()->loseItem('XOR', 1);
            $this->houseSimService->getState()->loseItem('Fiberglass Bow', 1);

            if($pet->hasMerit(MeritEnum::SOOTHING_VOICE))
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% engineered a Resonating Bow. They sang a soothing song as they plucked the last string, producing a Music Note.')
                    ->addInterestingness(PetActivityLogInterestingness::ActivityUsingMerit)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics' ]))
                ;

                $this->inventoryService->petCollectsItem('Music Note', $pet, $pet->getName() . ' produced this while engineered a Resonating Bow.', $activityLog);
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% engineered a Resonating Bow.')
                    ->addInterestingness(PetActivityLogInterestingness::HoHum + 18)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Science ], $activityLog);
            $this->inventoryService->petCollectsItem('Resonating Bow', $pet, $pet->getName() . ' engineered this.', $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to engineer a Resonating Bow, but couldn\'t get the harmonics logic right...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Science ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, false);
        }

        return $activityLog;
    }

    private function createAlienFishingRod(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngSkillRoll($petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getElectronicsBonus()->getTotal());

        if($roll >= 19)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Laser Pointer', 1);
            $this->houseSimService->getState()->loseItem('Alien Tissue', 1);
            $this->houseSimService->getState()->loseItem('Sylvan Fishing Rod', 1);
            $pet->increaseEsteem(5);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% integrated Alien Tissue into a Sylvan Fishing Rod using a Laser Pointer! (As you do!)')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 19)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics', 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Eridanus', $pet, $pet->getName() . ' scienced this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Science, PetSkillEnum::Crafts ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to integrate Alien Tissue with a Sylvan Fishing Rod, but the different forms of life kept rejecting one another...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics', 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Science, PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
        }

        return $activityLog;
    }

    private function createBuggerang(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngSkillRoll($petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getElectronicsBonus()->getTotal());

        if($roll <= 2)
        {
            if($pet->hasMerit(MeritEnum::SHOCK_RESISTANT))
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to electrify some gold, but it kept trying to zap them! ' . ActivityHelpers::PetName($pet) . '\'s shock-resistance protected them from any harm, but it was still annoying as heck.')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics', 'Smithing' ]))
                    ->addInterestingness(PetActivityLogInterestingness::ActivityUsingMerit)
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science, PetSkillEnum::Crafts ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
            }
            else
            {
                $pet->increaseSafety(-$this->rng->rngNextInt(4, 8));
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to electrify some gold, but accidentally zapped themselves, instead :(')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics', 'Smithing' ]))
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science, PetSkillEnum::Crafts ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
            }
        }
        else if($roll >= 18)
        {
            $this->houseSimService->getState()->loseItem('Lightning in a Bottle', 1);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Plastic Boomerang', 1);
            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% electrified some gold caps and attached them to a boomerang, creating a bug-zapping Buggerang!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 18)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics', 'Smithing' ]))
            ;
            $description = $this->rng->rngNextInt(1, 10) === 1 ? ($pet->getName() . ' scienced this. With SCIENCE.') : ($pet->getName() . ' scienced this.');
            $this->inventoryService->petCollectsItem('Buggerang', $pet, $description, $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Science, PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::SMITH, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% started to electrify some gold, but couldn\'t convince the gold to hold the charge...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics', 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science, PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    private function createAlienGun(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngSkillRoll($petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getElectronicsBonus()->getTotal());

        if($roll <= 2)
        {
            $pet->increaseSafety(-1);

            $pet->increasePsychedelic($this->rng->rngNextInt(1, 3));

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to engineer an Alien Gun, but accidentally breathed in a little bit of Magic Smoke! :O')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics', 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Magic Smoke', 1);
            $this->houseSimService->getState()->loseItem('Laser Pointer', 1);
            $this->houseSimService->getState()->loseItem('Toy Alien Gun', 1);
            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% rigged up a Toy Alien Gun to actually shoot lasers!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 15)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics', 'Magic-binding' ]))
            ;
            $this->inventoryService->petCollectsItem('Alien Gun', $pet, $pet->getName() . ' engineered this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Science, PetSkillEnum::Crafts ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% had an idea for how to make an Alien Gun using a Laser Pointer, but couldn\'t quite figure out the wiring...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics', 'Magic-binding' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science, PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
        }

        return $activityLog;
    }

    private function createDNA(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngSkillRoll($petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getElectronicsBonus()->getTotal());

        if($roll <= 2)
        {
            $pet->increaseSafety(-1);

            $pet->increasePsychedelic($this->rng->rngNextInt(1, 3));
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to improve a Lightning Sword, but accidentally breathed in a little bit of Magic Smoke! :O')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
        }
        else if($roll >= 20)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Magic Smoke', 1);
            $this->houseSimService->getState()->loseItem('Lightning Sword', 1);
            $this->houseSimService->getState()->loseItem('Alien Tissue', 1);
            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% added alien tech to a Lightning Sword, creating DNA!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 20)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics' ]))
            ;
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Science ], $activityLog);
            $this->inventoryService->petCollectsItem('DNA', $pet, $pet->getName() . ' engineered this.', $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% wanted to enhance a Lightning Sword with alien tech, but kept running into compatibility issues...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Science ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
        }

        return $activityLog;
    }

    private function createDIYTheremin(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $roll = $this->rng->rngSkillRoll(
            $petWithSkills->getIntelligence()->getTotal() +
            $petWithSkills->getDexterity()->getTotal() +
            $petWithSkills->getMusic()->getTotal() / 4 +
            $petWithSkills->getScience()->getTotal() * 3 / 4 +
            $petWithSkills->getElectronicsBonus()->getTotal()
        );

        if($roll <= 2)
        {
            $pet->increaseSafety(-1);

            $pet->increasePsychedelic($this->rng->rngNextInt(1, 3));
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a theremin, but accidentally breathed in a little bit of Magic Smoke! :O')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
        }
        else if($roll >= 18)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Magic Smoke', 1);
            $this->houseSimService->getState()->loseItem('Gold Tuning Fork', 1);
            $this->houseSimService->getState()->loseItem('Crooked Stick', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% reshaped a tuning fork into a DIY Theremin!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 20)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Science ], $activityLog);
            $this->inventoryService->petCollectsItem('DIY Theremin', $pet, $pet->getName() . ' engineered this.', $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% wanted to do something cool with a Gold Tuning Fork, but couldn\'t come up with anything concrete...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
        }

        return $activityLog;
    }
}
