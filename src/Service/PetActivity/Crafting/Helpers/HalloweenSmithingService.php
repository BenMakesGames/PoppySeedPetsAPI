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
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Model\ComputedPetSkills;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class HalloweenSmithingService
{
    public function __construct(
        private readonly PetExperienceService $petExperienceService,
        private readonly InventoryService $inventoryService,
        private readonly IRandom $rng,
        private readonly HouseSimService $houseSimService,
        private readonly EntityManagerInterface $em
    )
    {
    }

    public function createPumpkinBucket(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $buckets = [
            'Small, Yellow Plastic Bucket',
            'Upside-down, Yellow Plastic Bucket'
        ];

        $makes = ItemRepository::findOneByName($this->em, $this->rng->rngNextFromArray([
            'Ecstatic Pumpkin Bucket',
            'Distressed Pumpkin Bucket',
            'Unconvinced Pumpkin Bucket',
        ]));

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::SMITH, true);

            $itemUsed = $this->houseSimService->getState()->loseOneOf($this->rng, $buckets);
            $itemUsedItem = ItemRepository::findOneByName($this->em, $itemUsed);
            $pet->increaseEsteem(2);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% applied heat to ' . $itemUsedItem->getNameWithArticle() . ', and shaped it into ' . $makes->getNameWithArticle() . '!')
                ->setIcon('items/' . $makes->getImage())
                ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Crafting', 'Smithing', 'Special Event', 'Halloween' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);

            $this->inventoryService->petCollectsItem($makes, $pet, $pet->getName() . ' created this out of ' . $itemUsedItem->getNameWithArticle() . '.', $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to shape a bucket into ' . $makes->getNameWithArticle() . ', but couldn\'t get the heat just right.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Crafting', 'Smithing', 'Special Event', 'Halloween' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }
}
