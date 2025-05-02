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
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Exceptions\UnreachableException;
use App\Functions\ActivityHelpers;
use App\Functions\AdventureMath;
use App\Functions\ArrayFunctions;
use App\Functions\EquipmentFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class LostInTownService
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

        $activityLog = match($this->rng->rngNextInt(1, 4))
        {
            1 => $this->backAlley($petWithSkills),
            2 => $this->ruinedSettlement($petWithSkills),
            3 => $this->longSewerPipe($petWithSkills),
            4 => $this->magicDoor($petWithSkills),
            default => throw new UnreachableException()
        };

        $activityLog
            ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Adventure!' ]))
            ->setChanges($changes->compare($pet))
        ;

        if(AdventureMath::petAttractsBug($this->rng, $pet, 50))
            $this->inventoryService->petAttractsRandomBug($pet);

        return $activityLog;
    }

    private function backAlley(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        
        EquipmentFunctions::destroyPetTool($this->em, $pet);
        
        $items = [
            $this->rng->rngNextFromArray([ 'Canned Food', 'Spicy Tuna Salad Sammy', 'Soy Sauce', 'Korean Rice Cakes', 'Plastic Bottle' ]),
            $this->rng->rngNextFromArray([ 'Gold Ring', 'Key Ring', 'Laser Pointer' ]),
            $this->rng->rngNextFromArray([ 'Fluff', 'Iron Bar', 'Plastic', 'Glass', 'Tiny Scroll of Resources' ]),
        ];

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% followed the Woher Cuán Nani-nani, which lead them into a back alley in town. In some open boxes they found ' . ArrayFunctions::list_nice_sorted($items) . '. They looked around for a while, and scavenged up ' . ArrayFunctions::list_nice_sorted($items) . '. Afterwards, they realized they\'d lost the sign!')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]));

        $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::GATHER, true);

        foreach($items as $item)
            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' found this in a back alley in town.', $activityLog);

        return $activityLog;
    }

    private function ruinedSettlement(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        
        EquipmentFunctions::destroyPetTool($this->em, $pet);
        
        $items = [
            'Rusted, Busted Mechanism',
            $this->rng->rngNextFromArray([
                'Filthy Cloth', 'Canned Food', 'String', 'Iron Bar',
                'Naner', 'Yellowy Lime', 'Mango'
            ]),
        ];

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% followed the Woher Cuán Nani-nani, which lead them deep into the island\'s Micro-jungle, where they found a ruined settlement. They looked around for a while, and scavenged up ' . ArrayFunctions::list_nice_sorted($items) . '. Afterwards, they realized they\'d lost the sign!')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]));

        $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::GATHER, true);

        foreach($items as $item)
            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' found this in a ruined settlement deep in the island\'s Micro-jungle.', $activityLog);

        return $activityLog;
    }

    private function longSewerPipe(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($petWithSkills->getCanSeeInTheDark()->getTotal() <= 0)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% followed the Woher Cuán Nani-nani, which lead them to a long, dark sewer pipe. It was too dark to see anything, so they turned back.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Gathering,
                    PetActivityLogTagEnum::Dark
                ]));

            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(20, 40), PetActivityStatEnum::GATHER, false);
        }
        else
        {
            EquipmentFunctions::destroyPetTool($this->em, $pet);
            
            $items = [
                $this->rng->rngNextFromArray([ 'Black Scarf', 'Cool Sunglasses', 'Gaming Box', 'Password' ]),
                $this->rng->rngNextFromArray([ 'Glowing Protojelly', 'Green Egg', 'Thaumatoxic Cookies', 'Magic Smoke' ]),
                $this->rng->rngNextFromArray([ 'Diffie-H Key', 'Gold Keyblade', 'Rusty Blunderbuss', 'Yellow Can-opener' ]),
            ];

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% followed the Woher Cuán Nani-nani, which lead them to a long, dark sewer pipe. They used their ' . ActivityHelpers::SourceOfLight($petWithSkills) . ' to follow the pipe, eventually reaching its end, where they found a locker. Inside the locker was ' . ArrayFunctions::list_nice_sorted($items) . '. ' . ActivityHelpers::PetName($pet) . ' returned home with the items, realizing too late that they\'d left the sign behind!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                    PetActivityLogTagEnum::Gathering,
                    PetActivityLogTagEnum::Dark
                ]));

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 75), PetActivityStatEnum::GATHER, true);

            foreach($items as $item)
                $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' found this in a locker at the end of a long sewer pipe.', $activityLog);
        }

        return $activityLog;
    }

    private function magicDoor(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        
        EquipmentFunctions::destroyPetTool($this->em, $pet);
        
        $items = [
            'Cobweb',
            $this->rng->rngNextFromArray([ '"Roy" Plushy', 'Bulbun Plushy', 'Peacock Plushy', 'Rainbow Dolphin Plushy', 'Sneqo Plushy' ]),
            $this->rng->rngNextFromArray([ 'Metal Detector (Gold)', 'Graviton Gun', 'Ceremony of Shadows', 'Eridanus', 'Moonhammer', 'Debugger' ]),
            $this->rng->rngNextFromArray([ 'Box Box', 'Handicrafts Supply Box', 'Tile: Triple Chest', 'Minor Scroll of Riches' ]),
        ];

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% followed the Woher Cuán Nani-nani, which lead them to a door that didn\'t seem to go anywhere. They opened it up, and stepped inside, finding themselves in a small pocket dimension that looped in on itself. It seemed someone had made a home for themselves there, but long since abandoned it. ' . ActivityHelpers::PetName($pet) . ' rummaged around for a bit, and scavenged up ' . ArrayFunctions::list_nice_sorted($items) . ' before returning home. Afterwards, they realized they\'d left the sign behind, and were completely unable to find the door again!')
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering' ]));

        $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::ARCANA ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::GATHER, true);

        foreach($items as $item)
            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' found this in an abandoned pocket dimension behind a strange door somewhere in town.', $activityLog);

        return $activityLog;
    }
}
