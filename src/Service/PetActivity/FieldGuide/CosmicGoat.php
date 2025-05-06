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
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\SpiceRepository;
use App\Model\ComputedPetSkills;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class CosmicGoat implements FieldGuideAdventureInterface
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

        $cosmic = SpiceRepository::findOneByName($this->em, 'Cosmic');
        $creamyMilk = ItemRepository::findOneByName($this->em, 'Creamy Milk');

        foreach($petsWithSkills as $pet)
        {
            $skillRoll = $this->rng->rngNextInt(1, 20 + $pet->getArcana()->getTotal() + $pet->getDexterity()->getTotal() + $pet->getGatheringBonus()->getTotal() + $pet->getUmbraBonus()->getTotal());

            if($skillRoll >= 25)
                $milkCount = 3;
            else if($skillRoll >= 15)
                $milkCount = 2;
            else
                $milkCount = 1;

            $logText = count($petsWithSkills) === 1
                ? "$listOfPets went to one of the rivers that flows from the Cosmic Goat and collected $milkCount Creamy Milk!"
                : $listOfPets . ' went to one of the rivers that flows from the Cosmic Goat. ' . ActivityHelpers::PetName($pet->getPet()) . " collected $milkCount Creamy Milk!";

            $log = PetActivityLogFactory::createReadLog($this->em, $pet->getPet(), $logText)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::The_Umbra,
                    PetActivityLogTagEnum::Gathering,
                ]))
            ;

            $this->petExperienceService->gainExp($pet->getPet(), $milkCount, [ PetSkillEnum::ARCANA ], $log);

            for($i = 0; $i < $milkCount; $i++)
            {
                $item = $this->inventoryService->petCollectsEnhancedItem($creamyMilk, null, $cosmic, $pet->getPet(), $listOfPets . ' collected this from one of the rivers that flows from the Cosmic Goat.', $log, mayImmediatelyEatIfHungry: false);

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
            message: "$petNames went to one of the rivers that flows from the Cosmic Goat and scooped up some milk, collecting $lootList!",
            loot: $loot,
            tags: [
                PetActivityLogTagEnum::The_Umbra,
                PetActivityLogTagEnum::Gathering,
            ]
        );
    }
}