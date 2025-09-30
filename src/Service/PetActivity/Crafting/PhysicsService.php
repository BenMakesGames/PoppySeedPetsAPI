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

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ActivityHelpers;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\StatusEffectHelpers;
use App\Model\ActivityCallback;
use App\Model\ComputedPetSkills;
use App\Model\IActivityCallback;
use App\Model\PetChanges;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class PhysicsService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly IRandom $rng, private readonly PetExperienceService $petExperienceService,
        private readonly HouseSimService $houseSimService, private readonly EntityManagerInterface $em
    )
    {
    }

    /**
     * @return IActivityCallback[]
     */
    public function getCraftingPossibilities(ComputedPetSkills $petWithSkills): array
    {
        $possibilities = [];

        if($this->houseSimService->hasInventory('Tiny Black Hole') && $this->houseSimService->hasInventory('Worms'))
            $possibilities[] = new ActivityCallback($this->createWormhole(...), 10);

        if($this->houseSimService->hasInventory('Photon'))
            $possibilities[] = new ActivityCallback($this->createPoisson(...), 10);

        if($this->houseSimService->hasInventory('Lightning in a Bottle'))
        {
            if($this->houseSimService->hasInventory('Iron Sword'))
                $possibilities[] = new ActivityCallback($this->createLightningSword(...), 10);

            if($this->houseSimService->hasInventory('Gold Bar'))
            {
                if($this->houseSimService->hasInventory('Glass Pendulum'))
                    $possibilities[] = new ActivityCallback($this->createLivewire(...), 10);
            }

            if($this->houseSimService->hasInventory('Iron Bar') && $this->houseSimService->hasInventory('Plastic') && $this->houseSimService->hasInventory('Gravitational Waves'))
                $possibilities[] = new ActivityCallback($this->createGravitonGun(...), 10);
        }

        if($this->houseSimService->hasInventory('Gold Triangle') && $this->houseSimService->hasInventory('Seaweed') && $this->houseSimService->hasInventory('Gravitational Waves'))
            $possibilities[] = new ActivityCallback($this->createBermudaTriangle(...), 10);

        if($this->houseSimService->hasInventory('Snail Shell') && $this->houseSimService->hasInventory('Gravitational Waves') && $this->houseSimService->hasInventory('Crystal Ball'))
            $possibilities[] = new ActivityCallback($this->createGeodesicCurlicue(...), 10);

        return $possibilities;
    }

    /**
     * @param IActivityCallback[] $possibilities
     */
    public function adventure(ComputedPetSkills $petWithSkills, array $possibilities): PetActivityLog
    {
        if(count($possibilities) === 0)
            throw new \InvalidArgumentException('possibilities must contain at least one item.');

        $pet = $petWithSkills->getPet();

        /** @var IActivityCallback $method */
        $method = $this->rng->rngNextFromArray($possibilities);

        $changes = new PetChanges($pet);

        /** @var PetActivityLog $activityLog */
        $activityLog = $method->getCallable()($petWithSkills);

        $activityLog->setChanges($changes->compare($pet));

        return $activityLog;
    }

    private function doGravityMishapAdventure(Pet $pet, string $attemptedCraft): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::OTHER, null);
        $pet->increaseSafety(-$this->rng->rngNextInt(4, 8));
        $this->houseSimService->getState()->loseItem('Gravitational Waves', 1);

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% started to make a ' . $attemptedCraft . ', but the Gravitational Waves got out of control! The gravity inside the house was temporarily rotated 90 degrees, and rocks and other debris came crashing through the windows!!')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics', 'Smithing' ]))
            ->addInterestingness(PetActivityLogInterestingness::RareActivity)
        ;

        $randomNatureItem = $this->rng->rngNextFromArray([
            'Really Big Leaf',
            'Crooked Stick', 'Crooked Stick',
            'Coconut',
        ]);

        $this->inventoryService->petCollectsItem($randomNatureItem, $pet, 'This came crashing through the window when ' . $pet->getName() . ' accidentally rotated gravity around the house while handling some Gravitational Waves!', $activityLog);
        $this->inventoryService->petCollectsItem('Glass', $pet, 'This is the remains of one of your windows from when ' . $pet->getName() . ' accidentally rotated gravity around the house while handling some Gravitational Waves!', $activityLog);
        $this->inventoryService->petCollectsItem('Rock', $pet, 'This came crashing through the window when ' . $pet->getName() . ' accidentally rotated gravity around the house while handling some Gravitational Waves!', $activityLog);
        $this->inventoryService->petCollectsItem('Rock', $pet, 'This came crashing through the window when ' . $pet->getName() . ' accidentally rotated gravity around the house while handling some Gravitational Waves!', $activityLog);

        $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Science ], $activityLog);

        return $activityLog;
    }

    private function createPoisson(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getPhysicsBonus()->getTotal());

        if($roll <= 2)
        {
            $this->houseSimService->getState()->loseItem('Photon', 1);
            $pet->increasePoison(2);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to measure a Photon to confirm the Poisson Distribution, but a miscalculation produced a distribution of poison, instead! x_x')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Physics ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
        }
        else if($roll > 22)
        {
            $this->houseSimService->getState()->loseItem('Photon', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% measured a Photon, confirming the Poisson Distribution... and accidentally creating an X-ray in the process!')
                ->setIcon('items/space/x-ray')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 22)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Physics ]))
            ;

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Science ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, true);

            $this->inventoryService->petCollectsItem('Fish', $pet, $pet->getName() . ' measured a Photon, confirming the _Poisson_ Distribution!', $activityLog);
            $this->inventoryService->petCollectsItem('X-ray', $pet, $pet->getName() . ' measured a Photon, confirming the Poisson Distribution, and accidentally creating this X-ray in the process!', $activityLog);
        }
        else if($roll > 12)
        {
            $this->houseSimService->getState()->loseItem('Photon', 1);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% measured a Photon, confirming the Poisson Distribution!')
                ->setIcon('items/space/photon')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 12)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Physics ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, true);

            $this->inventoryService->petCollectsItem('Fish', $pet, $pet->getName() . ' measured a Photon, confirming the _Poisson_ Distribution!', $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to measure a Photon, but it kept zipping away before they could do so! >:(')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Physics ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
        }

        return $activityLog;
    }

    private function createWormhole(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getPhysicsBonus()->getTotal());

        if($roll <= 2 && $pet->getFood() < 4)
        {
            $this->houseSimService->getState()->loseItem('Worms', 1);
            $pet->increaseFood($this->rng->rngNextInt(3, 6));
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% started to create a Wormhole, but absentmindedly ate the Worms, instead :(')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Physics, 'Eating' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
        }
        else if($roll >= 17)
        {
            $this->houseSimService->getState()->loseItem('Tiny Black Hole', 1);
            $this->houseSimService->getState()->loseItem('Worms', 1);
            $pet->increaseEsteem($this->rng->rngNextInt(2, 4));
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Wormhole by inverting a Tiny Black Hole... and adding Worms?')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 17)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Physics ]))
            ;
            $this->inventoryService->petCollectsItem('Wormhole', $pet, $pet->getName() . ' created this from a Tiny Black Hole, and also Worms.' . ($this->rng->rngNextInt(1, 10) === 1 ? ' (SCIENCE.)' : ''), $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Science ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Wormhole, but the Worms kept crawling away, and %pet:' . $pet->getId() . '.name% wasted all their time gathering them back up again...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Physics ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
        }

        return $activityLog;
    }

    private function createLightningSword(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getPhysicsBonus()->getTotal());

        if($roll <= 2)
        {
            if($pet->hasMerit(MeritEnum::SHOCK_RESISTANT))
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to electrify an Iron Sword, but it kept trying to zap them! ' . ActivityHelpers::PetName($pet) . '\'s shock-resistance protected them from any harm, but it was still annoying as heck.')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Physics, PetActivityLogTagEnum::Crafting ]))
                    ->addInterestingness(PetActivityLogInterestingness::ActivityUsingMerit)
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            }
            else
            {
                $pet->increaseSafety(-$this->rng->rngNextInt(4, 8));
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to electrify an Iron Sword, but accidentally zapped themselves, instead :(')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Physics, PetActivityLogTagEnum::Crafting ]))
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            }
        }
        else if($roll >= 18)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Lightning in a Bottle', 1);
            $this->houseSimService->getState()->loseItem('Iron Sword', 1);
            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% electrified an Iron Sword; now it\'s a _Lightning_ Sword!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 18)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Physics, PetActivityLogTagEnum::Crafting ]))
            ;
            $description = $this->rng->rngNextInt(1, 10) === 1 ? ($pet->getName() . ' scienced this. With SCIENCE.') : ($pet->getName() . ' scienced this.');
            $this->inventoryService->petCollectsItem('Lightning Sword', $pet, $description, $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Science ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% started to electrify an Iron Sword, but couldn\'t convince the sword to hold the charge...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Physics, PetActivityLogTagEnum::Crafting ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createLivewire(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getPhysicsBonus()->getTotal());

        if($roll <= 2)
        {
            if($pet->hasMerit(MeritEnum::SHOCK_RESISTANT))
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to electrify a Glass Pendulum, but it kept trying to zap them! ' . ActivityHelpers::PetName($pet) . '\'s shock-resistance protected them from any harm, but it was still annoying as heck.')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Physics, PetActivityLogTagEnum::Crafting ]))
                    ->addInterestingness(PetActivityLogInterestingness::ActivityUsingMerit)
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            }
            else
            {
                $pet->increaseSafety(-$this->rng->rngNextInt(4, 8));
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to electrify a Glass Pendulum, but accidentally zapped themselves, instead :(')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Physics, PetActivityLogTagEnum::Crafting ]))
                ;

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            }
        }
        else if($roll >= 18)
        {
            $this->houseSimService->getState()->loseItem('Lightning in a Bottle', 1);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Glass Pendulum', 1);
            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% electrified a Glass Pendulum laced with gold, creating a Livewire!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 18)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Physics, PetActivityLogTagEnum::Crafting ]))
            ;
            $description = $this->rng->rngNextInt(1, 10) === 1 ? ($pet->getName() . ' scienced this. With SCIENCE.') : ($pet->getName() . ' scienced this.');
            $this->inventoryService->petCollectsItem('Livewire', $pet, $description, $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Science ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% started to electrify a Glass Pendulum, but couldn\'t convince the glass to hold the charge...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Physics, PetActivityLogTagEnum::Crafting ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    private function createBermudaTriangle(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($this->rng->rngNextInt(1, 200) == 1)
            return $this->doGravityMishapAdventure($pet, 'Bermuda Triangle');

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getPhysicsBonus()->getTotal());

        if($roll === 1)
        {
            $pet->increaseSafety(-6);
            StatusEffectHelpers::applyStatusEffect($this->em, $pet, StatusEffectEnum::HexHexed, 6 * 60);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Bermuda Triangle, but accidentally hexed themselves, instead! :(')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Physics, 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Science, PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
        }
        else if($roll >= 19)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Gold Triangle', 1);
            $this->houseSimService->getState()->loseItem('Seaweed', 1);
            $this->houseSimService->getState()->loseItem('Gravitational Waves', 1);
            $pet->increaseEsteem(5);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Bermuda Triangle out of a Gold Triangle!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 19)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Physics, 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Bermuda Triangle', $pet, $pet->getName() . ' scienced this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Science ], $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Bermuda Triangle, but the Gold Triangle kept getting bent by the gravitational forces, and ' . $pet->getName() . ' spent all their time bending it back into shape!')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Physics, 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Science ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
        }

        return $activityLog;
    }

    private function createGravitonGun(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($this->rng->rngNextInt(1, 200) == 1)
            return $this->doGravityMishapAdventure($pet, 'Graviton Gun');

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getPhysicsBonus()->getTotal());

        if($roll === 1)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);

            $pet->increaseSafety(-$this->rng->rngNextInt(4, 8));
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to engineer a Graviton Gun, but kept getting zapped by the Lightning in a Bottle! >:(')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics', 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Science, PetSkillEnum::Crafts ], $activityLog);
        }
        else if($roll >= 24)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Lightning in a Bottle', 1);
            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $this->houseSimService->getState()->loseItem('Iron Bar', 1);
            $this->houseSimService->getState()->loseItem('Gravitational Waves', 1);

            $pet->increaseEsteem(4);

            if($roll >= 34)
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Graviton Gun! Neat! And neater still, they had enough iron left over to make a Mini Satellite Dish!')
                    ->addInterestingness(PetActivityLogInterestingness::HoHum + 24)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics', 'Smithing' ]))
                ;

                $this->inventoryService->petCollectsItem('Mini Satellite Dish', $pet, $pet->getName() . ' made this out of the leftovers from making a Graviton Gun!', $activityLog);

                $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::Science, PetSkillEnum::Crafts ], $activityLog);
            }
            else
            {
                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a Graviton Gun! Neat!')
                    ->addInterestingness(PetActivityLogInterestingness::HoHum + 24)
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics', 'Smithing' ]))
                ;
                $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Science, PetSkillEnum::Crafts ], $activityLog);
            }

            $this->inventoryService->petCollectsItem('Graviton Gun', $pet, $pet->getName() . ' engineered this.', $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% had an idea for how to make a Graviton Gun, but couldn\'t quite figure out the physics...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Electronics', 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Science, PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
        }

        return $activityLog;
    }

    private function createGeodesicCurlicue(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($this->rng->rngNextInt(1, 200) == 1)
            return $this->doGravityMishapAdventure($pet, 'Geodesic Curlicue');

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getPhysicsBonus()->getTotal());

        if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::PROGRAM, true);
            $this->houseSimService->getState()->loseItem('Snail Shell', 1);
            $this->houseSimService->getState()->loseItem('Crystal Ball', 1);
            $this->houseSimService->getState()->loseItem('Gravitational Waves', 1);

            $pet->increaseEsteem(3);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% created a makeshift chirality resonance chamber using a Snail Shell of all things!')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 16)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Physics ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Science ], $activityLog);

            $this->inventoryService->petCollectsItem('Geodesic Curlicue', $pet, $pet->getName() . ' scienced this together. With science.', $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to create a makeshift chirality resonance chamber, but had trouble tuning the Snail Shell they were using.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Physics ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PROGRAM, false);
        }

        return $activityLog;
    }
}
