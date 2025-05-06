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

namespace App\Service\PetActivity\FieldGuide;

use App\Entity\User;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Model\ComputedPetSkills;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class HugeToad implements FieldGuideAdventureInterface
{
    public function __construct(
        private readonly IRandom $rng,
        private readonly InventoryService $inventoryService,
        private readonly EntityManagerInterface $em,
        private readonly PetExperienceService $petExperienceService,
    )
    {
        
    }

    /**
     * @param ComputedPetSkills[] $petsWithSkills
     */
    public function adventure(User $user, array $petsWithSkills): FieldGuideAdventureResults
    {
        $loot = [];

        $listOfPets = ArrayFunctions::list_nice(array_map(
            fn (ComputedPetSkills $pet) => ActivityHelpers::PetName($pet->getPet()),
            $petsWithSkills
        ));

        foreach($petsWithSkills as $pet)
        {
            $individualLoot = [];
            $skillRoll = $this->rng->rngNextInt(1, 20 + $pet->getArcana()->getTotal() + $pet->getDexterity()->getTotal() + $pet->getGatheringBonus()->getTotal() + $pet->getUmbraBonus()->getTotal());

            if($skillRoll >= 25)
                $individualLoot[] = 'Toad Legs';
            else if($skillRoll >= 15)
                $individualLoot[] = $this->rng->rngNextFromArray([ 'Toad Legs', 'Toadstool' ]);
            else
                $individualLoot[] = 'Toadstool';

            $logText = count($petsWithSkills) === 1
                ? $listOfPets . ' went into the woods and wrestled a Giant Toad. They got ' . ArrayFunctions::list_nice($individualLoot) . '.'
                : $listOfPets . ' went into the woods and wrestled a Giant Toad. ' . ActivityHelpers::PetName($pet->getPet()) . ' got ' . ArrayFunctions::list_nice($individualLoot) . '.';

            $log = PetActivityLogFactory::createReadLog($this->em, $pet->getPet(), $logText)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Fighting,
                    PetActivityLogTagEnum::Hunting,
                ]))
            ;

            $this->petExperienceService->gainExp($pet->getPet(), count($individualLoot), [ PetSkillEnum::BRAWL ], $log);

            foreach($individualLoot as $itemName)
            {
                $item = $this->inventoryService->petCollectsItem($itemName, $pet->getPet(), $listOfPets . ' collected this from one of the rivers that flows from the Cosmic Goat.', $log, mayImmediatelyEatIfHungry: false);

                if($item)
                    $loot[] = $item;
            }
        }

        $lootList = ArrayFunctions::list_nice(array_map(
            fn($item) => $item->getItem()->getName(),
            $loot
        ));

        $petNames = ArrayFunctions::list_nice(array_map(
            fn (ComputedPetSkills $pet) => $pet->getPet()->getName(),
            $petsWithSkills
        ));

        return new FieldGuideAdventureResults(
            message: "$petNames went into the woods and wrestled a Giant Toad, collecting $lootList!",
            loot: $loot,
            tags: [
                PetActivityLogTagEnum::Fighting,
                PetActivityLogTagEnum::Hunting,
            ]
        );
    }
}