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
use App\Enum\PetActivityLogTagEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetBadgeEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Model\ComputedPetSkills;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class TwuWuvCraftingService
{
    public function __construct(
        private readonly PetExperienceService $petExperienceService,
        private readonly InventoryService $inventoryService,
        private readonly CoinSmithingService $coinSmithingService,
        private readonly HouseSimService $houseSimService,
        private readonly SilverSmithingService $silverSmithingService,
        private readonly IRandom $rng,
        private readonly EntityManagerInterface $em
    )
    {
    }

    public function createWedBawwoon(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        $makingItem = ItemRepository::findOneByName($this->em, 'Wed Bawwoon');

        if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Red Balloon', 1);
            $this->houseSimService->getState()->loseItem('Twu Wuv', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made a Wed Bawwoon with the power of Twu Wuv!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts ], $activityLog);
            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' made this with the power of Twu Wuv!', $activityLog);
            PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::Wuvwy, $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Wed Bawwoon, but couldn\'t get the shape right...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createCupid(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($pet->hasMerit(MeritEnum::SILVERBLOOD))
            $roll += 5;

        $makingItem = ItemRepository::findOneByName($this->em, 'Cupid');

        if($roll <= 2)
        {
            if($this->rng->rngNextInt(1, 2) === 1)
            {
                $this->houseSimService->getState()->loseItem('String', 1);

                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to forge Cupid, but broke the String :(')
                    ->setIcon('icons/activity-logs/broke-string')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
                ;

                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
                $this->petExperienceService->gainExp($pet, 1, [PetSkillEnum::Crafts], $activityLog);

                return $activityLog;
            }
            else
            {
                $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

                if($roll >= 12)
                    return $this->coinSmithingService->makeSilverCoins($petWithSkills, $makingItem);
                else
                    return $this->silverSmithingService->spillSilver($petWithSkills, $makingItem);
            }
        }
        else if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Silver Bar', 1);
            $this->houseSimService->getState()->loseItem('Twu Wuv', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% forged Cupid with the power of Twu Wuv!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Crafts ], $activityLog);
            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' forged this with the power of Twu Wuv!', $activityLog);
            PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::Wuvwy, $activityLog);
            return $activityLog;
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to forge Cupid, but couldn\'t get the shape right...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Crafting, PetActivityLogTagEnum::Location_At_Home ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Crafts ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);

            return $activityLog;
        }
    }
}
