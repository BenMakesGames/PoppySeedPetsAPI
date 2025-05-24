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


namespace App\Service\PetActivity\Crafting\Helpers;

use App\Entity\PetActivityLog;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogTagHelpers;
use App\Model\ComputedPetSkills;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

class GoldSmithingService
{
    public function __construct(
        private readonly PetExperienceService $petExperienceService,
        private readonly InventoryService $inventoryService,
        private readonly ResponseService $responseService,
        private readonly HouseSimService $houseSimService,
        private readonly CoinSmithingService $coinSmithingService,
        private readonly EntityManagerInterface $em,
        private readonly IRandom $rng
    )
    {
    }

    public function createGoldTriangle(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getMusic()->getTotal()) + $petWithSkills->getSmithingBonus()->getTotal());

        $makingItem = ItemRepository::findOneByName($this->em, 'Gold Triangle');

        if($roll <= 2)
        {
            $reRoll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

            if($reRoll >= 12)
                return $this->coinSmithingService->makeGoldCoins($petWithSkills, $makingItem);
            else
                return $this->coinSmithingService->spillGold($petWithSkills, $makingItem);
        }
        else if($roll >= 13)
        {
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);

            $pet->increaseEsteem(1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Gold Triangle from Gold Bar, and String.', 'items/tool/instrument/triangle-gold')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 13)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' created this from Gold Bar, and String.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts, PetSkillEnum::Music ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Gold Triangle, but couldn\'t figure it out.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createAubergineScepter(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        $makingItem = ItemRepository::findOneByName($this->em, 'Aubergine Scepter');

        if($roll <= 2)
        {
            if($this->rng->rngNextBool())
            {
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);

                $this->houseSimService->getState()->loseItem('Eggplant', 1);

                $pet->increaseSafety(-$this->rng->rngNextInt(4, 8));

                if($pet->hasMerit(MeritEnum::GOURMAND))
                {
                    $pet->increaseFood($this->rng->rngNextInt(4, 8));

                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make an Aubergine Scepter, but accidentally burnt the Eggplant! %pet:' . $pet->getId() . '.name%, as a true gourmand, could not allow even an Eggplant to go to waste, and ate it!', '')
                        ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing', 'Eating', 'Gourmand' ]))
                    ;
                }
                else
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make an Aubergine Scepter, but accidentally burned the Eggplant. It smelled _awful_!', '')
                        ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
                    ;
                }

                $this->petExperienceService->gainExp($pet, 1, [PetSkillEnum::Crafts], $activityLog);

                return $activityLog;
            }
            else
            {
                $reRoll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

                if($reRoll >= 12)
                    return $this->coinSmithingService->makeGoldCoins($petWithSkills, $makingItem);
                else
                    return $this->coinSmithingService->spillGold($petWithSkills, $makingItem);
            }
        }
        else if($roll >= 12)
        {
            $this->houseSimService->getState()->loseItem('Eggplant', 1);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);

            $pet->increaseEsteem(1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made an Aubergine Scepter.', 'items/tool/wand/aubergine-scepter')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 13)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' created... _this_.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::SMITH, true);
            return $activityLog;
        }
        else
        {
            if($this->rng->rngNextInt(1, 10) === 1)
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make an Aubergine Scepter, but couldn\'t figure it out. (It\'s probably for the better.)', 'icons/activity-logs/confused');
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make an Aubergine Scepter, but couldn\'t figure it out.', 'icons/activity-logs/confused');

            $activityLog->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]));

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::SMITH, false);

            return $activityLog;
        }
    }

    public function createMoonhammer(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 20)
        {
            $this->houseSimService->getState()->loseItem('Fiberglass', 1);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Moon Pearl', 1);

            $pet->increaseEsteem(3);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Moonhammer!', 'items/tool/scythe')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 20)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Moonhammer', $pet, $pet->getName() . ' created this from Fiberglass, gold, and a Moon Pearl.', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make something out of Fiberglass, but wasn\'t happy with how it was turning out.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createVicious(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 16)
        {
            $this->houseSimService->getState()->loseItem('Blackonite', 1);
            $this->houseSimService->getState()->loseItem('White Cloth', 1);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $pet->increaseEsteem(3);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made Vicious! (Scary...)', 'items/tool/sword/vicious')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 16)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Vicious', $pet, $pet->getName() . ' forged this blade from gold and Blackonite!', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make an evil-looking sword, but couldn\'t get it lookin\' _evil_ enough...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createKundravsStandard(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 20)
        {
            $this->houseSimService->getState()->loseItem('Dragon Flag', 1);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Dark Scales', 1);
            $pet->increaseEsteem(3);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made Kundrav\'s Standard!', 'items/tool/scythe')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 20)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Kundrav\'s Standard', $pet, $pet->getName() . ' created this using Dark Scales, a Gold Bar, and a Dragon Flag!', $activityLog);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to improve a Dragon Flag, but couldn\'t come up with any cool ideas.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createFungalClarinet(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2 && $pet->getFood() < 8)
        {
            $this->houseSimService->getState()->loseItem('Chanterelle', 1);
            $pet->increaseFood(4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started making a Fungal Clarinet, but ended up eating the Chanterelle...', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing', 'Eating' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }
        else if($roll >= 15)
        {
            $this->houseSimService->getState()->loseItem('Flute', 1);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Chanterelle', 1);

            $pet->increaseEsteem(3);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Fungal Clarinet with gold keys! (But not Gold Keys.)', 'items/tool/instrument/clarinet-fungal')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 15)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Fungal Clarinet', $pet, $pet->getName() . ' created this!', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% thought it might be cool to decorate a Flute, but couldn\'t think of something stylish enough.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createCoreopsis(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 15)
        {
            $this->houseSimService->getState()->loseItem('Plastic Shovel', 1);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Green Dye', 1);

            $pet->increaseEsteem(2);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% upgraded a Plastic Shovel into Coreopsis!', 'items/tool/shovel/coreopsis')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 15)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);

            $this->inventoryService->petCollectsItem('Coreopsis', $pet, $pet->getName() . ' created this!', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% thought it might be cool to upgrade a Plastic Shovel, but couldn\'t come up with anything...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createGoldKey(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        $makingItem = ItemRepository::findOneByName($this->em, 'Gold Key');

        if($roll <= 2)
        {
            $reRoll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

            if($reRoll >= 12)
                return $this->coinSmithingService->makeGoldCoins($petWithSkills, $makingItem);
            else
                return $this->coinSmithingService->spillGold($petWithSkills, $makingItem);
        }

        if($roll >= 12)
        {
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);

            $keys = $roll >= 29 ? 2 : 1;

            if($keys === 2)
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% forged *two* Gold Keys from a Gold Bar!', 'items/key/gold');
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% forged a Gold Key from a Gold Bar.', 'items/key/gold');

            $activityLog->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]));

            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' forged this from a Gold Bar.', $activityLog);

            if($keys === 2)
                $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' forged this from a Gold Bar.', $activityLog);

            $this->petExperienceService->gainExp($pet, $keys, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);

            $pet->increaseEsteem($keys === 1 ? 1 : 10);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to forge a Gold Key from a Gold Bar, but couldn\'t get the shape right.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createGoldTuningFork(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        $makingItem = ItemRepository::findOneByName($this->em, 'Gold Tuning Fork');

        if($roll <= 2)
        {
            $reRoll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

            if($reRoll >= 12)
                return $this->coinSmithingService->makeGoldCoins($petWithSkills, $makingItem);
            else
                return $this->coinSmithingService->spillGold($petWithSkills, $makingItem);
        }

        if($roll >= 13)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% forged a Gold Tuning Fork from a Gold Bar.', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' forged this from a Gold Bar.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts ], $activityLog);
            $pet->increaseEsteem(2);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to forge a Gold Tuning Fork from a Gold Bar, but couldn\'t get the shape right.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createGoldTelescope(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll === 1)
        {
            $pet->increaseSafety(-4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make a lens from a piece of glass, but cut themselves! :(', 'icons/activity-logs/wounded')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }
        else if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Glass', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Gold Telescope.', '')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 15)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Gold Telescope', $pet, $pet->getName() . ' created this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Crafts ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a telescope, but almost broke the glass, and gave up.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createGoldCompass(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll === 1)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-$this->rng->rngNextInt(2, 8));
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to work gold into an Enchanted Compass, but got burned while trying! :(', 'icons/activity-logs/burn')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Crafts ], $activityLog);
        }
        else if($roll >= 20)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Enchanted Compass', 1);
            $pet->increaseEsteem(3);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Gold Compass.', '')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 20)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->inventoryService->petCollectsItem('Gold Compass', $pet, $pet->getName() . ' created this.', $activityLog);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Crafts ], $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to work gold into an Enchanted Compass, but the enchantment resisted any change.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createGoldRod(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $smithingRoll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());
        $printerRoll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        $makingItem = ItemRepository::findOneByName($this->em, 'Gold Rod');

        if($printerRoll <= 3)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Gold Rod, but the 3D Printer started acting, and %pet:' . $pet->getId() . '.name% ended up spending all their time rechecking wires and software settings...', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing', '3D Printing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science, PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::PLASTIC_PRINT, false);

            return $activityLog;
        }

        if($smithingRoll <= 2)
        {
            $reRoll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

            if($reRoll >= 12)
                return $this->coinSmithingService->makeGoldCoins($petWithSkills, $makingItem);
            else
                return $this->coinSmithingService->spillGold($petWithSkills, $makingItem);
        }

        if($smithingRoll >= 16)
        {
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Plastic', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Gold Rod from Plastic and a Gold Bar.', '')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 16)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing', '3D Printing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Science, PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);

            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' created this from Plastic and a Gold Bar.', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Gold Rod, but almost burnt themselves, and gave up.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing', '3D Printing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Science, PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createSilverKeyblade(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Silver Key', 1);
            $this->houseSimService->getState()->loseItem('White Cloth', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Silver Keyblade.', '')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 15)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Crafts ], $activityLog);
            $this->inventoryService->petCollectsItem('Silver Keyblade', $pet, $pet->getName() . ' created this.', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a keyblade, but couldn\'t get the hilt right...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createNoWhiskNoReward(ComputedPetSkills  $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        $makingItem = ItemRepository::findOneByName($this->em, 'No Whisk, No Reward');

        if($roll <= 2)
        {
            $reRoll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

            if($reRoll >= 12)
                return $this->coinSmithingService->makeGoldCoins($petWithSkills, $makingItem);
            else
                return $this->coinSmithingService->spillGold($petWithSkills, $makingItem);
        }
        else if($roll >= 24)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Firestone', 1);
            $this->houseSimService->getState()->loseItem('Culinary Knife', 1);
            $pet->increaseEsteem($this->rng->rngNextInt(2, 4));

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% smithed No Whisk, No Reward.', '')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 24)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::Crafts ], $activityLog);
            $this->inventoryService->petCollectsItem('No Whisk, No Reward', $pet, $pet->getName() . ' smithed this with the magic heat of a Firestone!', $activityLog);

            if($pet->hasMerit(MeritEnum::METATRON_S_TOUCH))
                $this->inventoryService->petCollectsItem('Rock', $pet, 'This was left over after ' . $pet->getName() . ' worked with Firestone.', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make No Whisk, No Reward, but couldn\'t get the hilt right...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createSiderealLeafSpear(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Iron Bar', 1);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Leaf Spear', 1);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% decorated a Leaf Spear with a little sun and moon, creating a Sidereal Leaf Spear!', '')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 16)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing', 'Crafting' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Crafts ], $activityLog);
            $this->inventoryService->petCollectsItem('Sidereal Leaf Spear', $pet, $pet->getName() . ' created this by attaching a little gold sun and iron moon to a Leaf Spear.', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a little gold sun and iron moon, but couldn\'t get the shapes just right...', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing', 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createGildedMericarp(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 22)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Mericarp', 1);
            $pet->increaseEsteem(4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a Gilded Mericarp!', '')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 22)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing', 'Crafting' ]))
            ;
            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::Crafts ], $activityLog);
            $this->inventoryService->petCollectsItem('Gilded Mericarp', $pet, $pet->getName() . ' created this by gilding a Mericarp.', $activityLog);
        }
        else if($roll == 1)
        {
            $this->houseSimService->getState()->loseItem('Mericarp', 1);
            $pet->increaseEsteem(-4);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to gild a Mericarp, but messed up and reduced it to Charcoal! >:(', '')
                ->addInterestingness(PetActivityLogInterestingness::HoHum + 22)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing', 'Crafting' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
            $this->inventoryService->petCollectsItem('Charcoal', $pet, $pet->getName() . ' accidentally reduced a Mericarp to this...', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to gild a Mericarp, but almost accidentally burned it to a crisp, instead!', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Smithing', 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }
}
