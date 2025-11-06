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


namespace App\Service\PetActivity\Daydreams;

use App\Entity\PetActivityLog;
use App\Enum\MeritEnum;
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
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class FoodFightDaydream
{
    public function __construct(
        private readonly IRandom $rng,
        private readonly EntityManagerInterface $em,
        private readonly InventoryService $inventoryService,
        private readonly PetExperienceService $petExperienceService
    )
    {
    }

    public function doAdventure(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $changes = new PetChanges($pet);

        $adventures = [
            $this->doChocolateDragon(...),
            $this->doCombatOnPowderedSugarBeach(...),
            $this->doPuddingBeastsInCustardCanyon(...),
            $this->doVegetableWarriors(...),
        ];

        $log = $this->rng->rngNextFromArray($adventures)($petWithSkills);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);

        $log
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Dream ]))
            ->setIcon('icons/status-effect/daydream-food-fight')
            ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
            ->setChanges($changes->compare($pet));

        return $log;
    }

    private function doChocolateDragon(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasMerit(MeritEnum::GOURMAND))
        {
            $log = PetActivityLogFactory::createUnreadLog(
                $this->em,
                $petWithSkills->getPet(),
                ActivityHelpers::PetName($pet) . ' daydreamed they encountered a chocolate dragon! It roared and flapped its wings menacingly, but ' . ActivityHelpers::PetName($pet) . ' just started eating it, starting with its legs! (Ah~! A true Gourmand!) Midway into finishing the dragon, ' . ActivityHelpers::PetName($pet) . ' snapped back to reality, a piece of the dragon still in their hands...');

            $log->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gourmand', 'Eating' ]));

            $pet->increaseFood(8)->increaseEsteem(4);

            $this->inventoryService->petCollectsItem('Chocolate Bar', $pet, 'The remains of a chocolate dragon which ' . $pet->getName() . ' ate in a daydream.', $log);

            PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::PulledAnItemFromADream, $log);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Brawl ], $log);

            return $log;
        }

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl()->getTotal());

        if($roll >= 15)
        {
            $log = PetActivityLogFactory::createUnreadLog(
                $this->em,
                $petWithSkills->getPet(),
                ActivityHelpers::PetName($pet) . ' daydreamed they encountered a chocolate dragon! The two battled fiercely, but ' . ActivityHelpers::PetName($pet) . ' was victorious! When they snapped back to reality, they were surrounded by chocolately dragon remains...');

            $this->inventoryService->petCollectsItem('Chocolate Bar', $pet, 'The remains of a chocolate dragon which ' . $pet->getName() . ' defeated in a daydream.', $log);

            switch($this->rng->rngNextInt(1, 5))
            {
                case 1:
                case 2:
                    $this->inventoryService->petCollectsItem('Cocoa Powder', $pet, 'The remains of a chocolate dragon which ' . $pet->getName() . ' defeated in a daydream.', $log);
                    $this->inventoryService->petCollectsItem('Spicy Chocolate Bar', $pet, 'The remains of a chocolate dragon which ' . $pet->getName() . ' defeated in a daydream.', $log);
                    break;

                case 3:
                case 4:
                    $this->inventoryService->petCollectsItem('Cocoa Powder', $pet, 'The remains of a chocolate dragon which ' . $pet->getName() . ' defeated in a daydream.', $log);
                    $this->inventoryService->petCollectsItem('Chocomilk', $pet, 'The "blood" of a chocolate dragon which ' . $pet->getName() . ' defeated in a daydream.', $log);
                    break;

                case 5:
                    $this->inventoryService->petCollectsItem('Chocolate Cue Ball', $pet, 'The, uh, \*cough\* _remains_ of a chocolate dragon which ' . $pet->getName() . ' defeated in a daydream.', $log);
                    $this->inventoryService->petCollectsItem('Chocolate Cue Ball', $pet, 'The, uh, \*cough\* _remains_ of a chocolate dragon which ' . $pet->getName() . ' defeated in a daydream.', $log);
                    break;
            }

            PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::PulledAnItemFromADream, $log);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Brawl ], $log);
            $pet->increaseEsteem(4);

            return $log;
        }
        else
        {
            $log = PetActivityLogFactory::createUnreadLog(
                $this->em,
                $petWithSkills->getPet(),
                ActivityHelpers::PetName($pet) . ' daydreamed they encountered a chocolate dragon! The two battled fiercely, but a giant piece of the dragon fell on ' . ActivityHelpers::PetName($pet) . ', causing them to snap back to reality...');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Brawl ], $log);

            $this->inventoryService->petCollectsItem('Chocolate Bar', $pet, 'A piece of a chocolate dragon than fell on ' . $pet->getName() . ' in a daydream.', $log);

            PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::PulledAnItemFromADream, $log);

            return $log;
        }
    }

    private function doCombatOnPowderedSugarBeach(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $log = PetActivityLogFactory::createUnreadLog(
            $this->em,
            $petWithSkills->getPet(),
            ActivityHelpers::PetName($pet) . ' daydreamed they were on a moonlit beach of powdered sugar, dancing a swirling duel against an opponent. With every step, powdered sugar was kicked up into the air. ' . ActivityHelpers::PetName($pet) . '\'s vision went white, then they snapped back to reality, sugar falling off their body.');

        $this->inventoryService->petCollectsItem('Sugar', $pet, $pet->getName() . ' got covered by this in a daydream.', $log);
        $this->inventoryService->petCollectsItem('Sugar', $pet, $pet->getName() . ' got covered by this in a daydream.', $log);

        if($this->rng->rngNextBool())
            $this->inventoryService->petCollectsItem('Sugar', $pet, $pet->getName() . ' got covered by this in a daydream.', $log);

        PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::PulledAnItemFromADream, $log);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Brawl ], $log);

        return $log;
    }

    private function doPuddingBeastsInCustardCanyon(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $possibleLoot = [
            'Mango Pudding', 'Naner Puddin\'', 'Rice Puddin\'',
            'Caramel', 'Caramel'
        ];

        $loot = $this->rng->rngNextSubsetFromArray($possibleLoot, $this->rng->rngNextInt(1, $this->rng->rngNextInt(2, 3)));

        $log = PetActivityLogFactory::createUnreadLog(
            $this->em,
            $petWithSkills->getPet(),
            ActivityHelpers::PetName($pet) . ' daydreamed they were trekking through a treacherous canyon, battling pudding beasts and caramel creatures that lurked in the shadows! When they snapped back to reality, they were holding ' . ArrayFunctions::list_nice_sorted($loot) . '!');

        foreach($loot as $itemName)
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' found this in a daydream.', $log);

        PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::PulledAnItemFromADream, $log);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Brawl ], $log);

        return $log;
    }

    private function doVegetableWarriors(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $log = PetActivityLogFactory::createUnreadLog(
            $this->em,
            $petWithSkills->getPet(),
            ActivityHelpers::PetName($pet) . ' daydreamed they were in a vast, echoing hall; warriors stood poised with delicate fans that, when unfurled, had the gleam and sharpness of knife-sliced vegetables. ' . ActivityHelpers::PetName($pet) . ' grabbed some of the vegetables, then snapped back to reality, vegetables still in hand.');

        $this->inventoryService->petCollectsItem('Pickled Veggie Slices', $pet, $pet->getName() . ' found this in a daydream.', $log);
        $this->inventoryService->petCollectsItem('Pickled Veggie Slices', $pet, $pet->getName() . ' found this in a daydream.', $log);

        PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::PulledAnItemFromADream, $log);

        return $log;
    }
}