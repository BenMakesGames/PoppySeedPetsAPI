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
use App\Enum\PetBadgeEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Model\ComputedPetSkills;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class FructalPlaneService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly InventoryService $inventoryService,
        private readonly IRandom $rng,
        private readonly PetExperienceService $petExperienceService
    ) {
    }

    public function adventure(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $roll = $this->rng->rngNextInt(1,
            20 +
            $petWithSkills->getDexterity()->getTotal() +
            $petWithSkills->getArcana()->getTotal() +
            max(
                $petWithSkills->getGatheringBonus()->getTotal(),
                $petWithSkills->getUmbraBonus()->getTotal()
            )
        );

        $pet = $petWithSkills->getPet();

        $loot = [];

        if($roll >= 20)
            $loot[] = 'Evilberries';

        while(count($loot) < ($roll - 10) / 8)
        {
            $loot[] = $this->rng->rngNextFromArray([
                'Apricot', 'Blackberries', 'Blueberries', 'Cacao Fruit', 'Fig', 'Honeydont',
                'Mango', 'Melowatern', 'Naner', 'Orange', 'Pamplemousse', 'Pineapple',
                'Red', 'Red Pear', 'Yellowy Lime',

                'Apricot Preserves', 'Blackberry Jam', 'Blueberry Jam', 'Naner Preserves',
                'Orange Marmalade', 'Pamplemousse Marmalade', 'Red Marmalade',

                'Fig Leaf', 'Pectin', 'Quintessence'
            ]);
        }

        if(count($loot) === 0)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' was thinking about what to do, when they were suddenly sucked into their ' . $pet->getTool()->getFullItemName() . ', and into the Fructal Plane! It all happened so quickly, ' . ActivityHelpers::PetName($pet) . ' wasn\'t able to get their bearings until they were ejected back to the physical world.')
                ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::The_Umbra,
                    PetActivityLogTagEnum::Location_The_Fructal_Plane,
                ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, 2, PetActivityStatEnum::UMBRA, false);

            PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::VisitedTheFructalPlane, $activityLog);

            return $activityLog;
        }

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% was thinking about what to do, when they were suddenly sucked into their ' . $pet->getTool()->getFullItemName() . ', and into the Fructal Plane! They grabbed what they could before being ejected back to the physical world: ' . ArrayFunctions::list_nice_sorted($loot) . '.')
            ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                PetActivityLogTagEnum::The_Umbra,
                PetActivityLogTagEnum::Location_The_Fructal_Plane,
            ]))
        ;

        foreach($loot as $item)
            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' found this during a brief visit to the Fructal Plane.', $activityLog);

        $pet->increaseEsteem($this->rng->rngNextInt(3 + count($loot), 5 + count($loot)));

        $this->petExperienceService->gainExp($pet, count($loot), [ PetSkillEnum::Arcana ], $activityLog);
        $this->petExperienceService->spendTime($pet, 2, PetActivityStatEnum::UMBRA, true);

        $pet->getTool()->setEnchantment(null);

        PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::VisitedTheFructalPlane, $activityLog);

        return $activityLog;
    }
}