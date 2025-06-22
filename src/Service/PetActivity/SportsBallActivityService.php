<?php
declare(strict_types = 1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Service\PetActivity;

use App\Entity\PetActivityLog;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ActivityHelpers;
use App\Functions\EnchantmentRepository;
use App\Functions\EquipmentFunctions;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use App\Enum\PetBadgeEnum;
use App\Service\InventoryService;

class SportsBallActivityService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly IRandom $rng,
        private readonly PetExperienceService $petExperienceService,
        private readonly TransactionService $transactionService,
        private readonly InventoryService $inventoryService
    )
    {
    }

    public function doOrangeSportsballBall(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        
        $changes = new PetChanges($pet);

        // Calculate skill check based on pet's dexterity and brawl skills
        $skillCheck = $this->rng->rngNextInt(1, 20) + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl()->getTotal();

        [$outcome, $moneyAwarded, $expAwarded] = match(true) {
            $skillCheck >= 18 => ['three_point', 3, 3],
            $skillCheck >= 12 => ['two_point', 2, 2],
            default => ['miss', 0, 1],
        };

        // Create activity log based on outcome
        $activityLog = match($outcome) {
            'three_point' => PetActivityLogFactory::createUnreadLog(
                $this->em, 
                $pet, 
                ActivityHelpers::PetName($pet) . ' did a 3-point hoop, whatever that means. But apparently it\'s worth 3m? (Sportsball is so confusing...)'
            ),
            'two_point' => PetActivityLogFactory::createUnreadLog(
                $this->em, 
                $pet, 
                ActivityHelpers::PetName($pet) . ' did a 2-point hoop, whatever that means. But apparently it\'s worth 2m? (Sportsball is so confusing...)'
            ),
            'miss' => PetActivityLogFactory::createUnreadLog(
                $this->em, 
                $pet, 
                ActivityHelpers::PetName($pet) . ' tried to "do a hoop" with their Orange Sportsball Ball but apparently missed all the hoops completely??? (Sportsball is so confusing...)'
            ),
            default => throw new \Exception('Unexpected outcome in Orange Sportsball Ball activity')
        };

        if($moneyAwarded > 0)
        {
            $this->transactionService->getMoney(
                $pet->getOwner(), 
                $moneyAwarded, 
                $pet->getName() . ' scored a ' . $moneyAwarded . '-point hoop!'
            );
        }

        // Award badge for 3-point hoop
        if($outcome === 'three_point')
        {
            PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::Hoopmaster, $activityLog);
        }

        $this->petExperienceService->gainExp($pet, $expAwarded, [PetSkillEnum::Brawl], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::OTHER, null);

        // Destroy the Orange Sportsball Ball
        EquipmentFunctions::destroyPetTool($this->em, $pet);

        // Set up the activity log
        $activityLog
            ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                PetActivityLogTagEnum::Adventure,
                PetActivityLogTagEnum::Sportsball
            ]))
            ->setChanges($changes->compare($pet))
        ;
        
        return $activityLog;
    }

    public function doSportsballPin(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        
        $changes = new PetChanges($pet);

        // Calculate skill check based on pet's dexterity and brawl skills
        $skillCheck = $this->rng->rngNextInt(1, 20) + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl()->getTotal();

        [$outcome, $expAwarded] = match(true) {
            $skillCheck >= 18 => ['perfect', 3],
            default => ['miss', 1],
        };

        // Create activity log based on outcome
        $activityLog = match($outcome) {
            'perfect' => PetActivityLogFactory::createUnreadLog(
                $this->em, 
                $pet, 
                ActivityHelpers::PetName($pet) . ' went skittling with their Sportsball Pin and got a strike on a rainbow? (Sportsball is so confusing...)'
            ),
            'miss' => PetActivityLogFactory::createUnreadLog(
                $this->em, 
                $pet, 
                ActivityHelpers::PetName($pet) . ' went skittling with their Sportsball Pin and got a homerun, which apparently is a _bad_ thing when skittling? (Sportsball is so confusing...)'
            ),
            default => throw new \Exception('Unexpected outcome in Sportsball Pin activity')
        };

        // Award Rainbow item for perfect score
        if($outcome === 'perfect')
        {
            $this->inventoryService->petCollectsItem('Rainbow', $pet, $pet->getName() . ' struck this while skittling!', $activityLog);
            PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::TasteTheRainbow, $activityLog);
        }

        $this->petExperienceService->gainExp($pet, $expAwarded, [PetSkillEnum::Brawl], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::OTHER, null);

        // Destroy the Sportsball Pin
        EquipmentFunctions::destroyPetTool($this->em, $pet);

        // Set up the activity log
        $activityLog
            ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                PetActivityLogTagEnum::Adventure,
                PetActivityLogTagEnum::Sportsball
            ]))
            ->setChanges($changes->compare($pet))
        ;
        
        return $activityLog;
    }

    public function doSportsballOar(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        
        $changes = new PetChanges($pet);

        // Calculate skill check based on pet's dexterity and brawl or crafts skill
        $skillCheck = $this->rng->rngNextInt(1, 20) + $petWithSkills->getDexterity()->getTotal() + max($petWithSkills->getBrawl()->getTotal(), $petWithSkills->getCrafts()->getTotal());

        [$outcome, $expAwarded] = match(true) {
            $skillCheck >= 18 => ['victory', 3],
            default => ['defeat', 1],
        };

        // Transform the oar into a wooden sword
        $pet->getTool()
            ->changeItem(ItemRepository::findOneByName($this->em, 'Wooden Sword'))
            ->setEnchantment(EnchantmentRepository::findOneByName($this->em, 'Racketing'))
            ->addComment($pet->getName() . ' carved this from a Sportsball Oar during a Sportsball duel!')
        ;

        // Create activity log based on outcome
        $activityLog = match($outcome) {
            'victory' => PetActivityLogFactory::createUnreadLog(
                $this->em, 
                $pet, 
                ActivityHelpers::PetName($pet) . ' carved their Sportsball Oar into a wooden sword and dueled another sportsballer. They won, capturing their opponent\'s flag! (Sportsball is so confusing...)'
            ),
            'defeat' => PetActivityLogFactory::createUnreadLog(
                $this->em, 
                $pet, 
                ActivityHelpers::PetName($pet) . ' carved their Sportsball Oar into a wooden sword and dueled another sportsballer. Unfortunately, they lost the duel. (Sportsball is so confusing...)'
            ),
            default => throw new \Exception('Unexpected outcome in Sportsball Oar activity')
        };

        // Award White Flag for victory
        if($outcome === 'victory')
        {
            $this->inventoryService->petCollectsItem('White Flag', $pet, $pet->getName() . ' captured this from their defeated opponent in a Sportsball duel!', $activityLog);
            PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::Musashi, $activityLog);
        }

        $this->petExperienceService->gainExp($pet, $expAwarded, [PetSkillEnum::Brawl, PetSkillEnum::Crafts], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 45), PetActivityStatEnum::OTHER, null);

        // Set up the activity log
        $activityLog
            ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                PetActivityLogTagEnum::Adventure,
                PetActivityLogTagEnum::Sportsball
            ]))
            ->setChanges($changes->compare($pet))
        ;
        
        return $activityLog;
    }

    public function doGreenSportsballBall(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        
        $changes = new PetChanges($pet);

        // Randomly select one of the three possible items
        $revealedItem = ItemRepository::findOneByName($this->em, $this->rng->rngNextFromArray([
            'Beans',
            'Donut Holes',
            'Egg',
            'Falafel',
            'Fluff',
            'Fortune Cookie',
            'Glowing Six-sided Die',
            'Gulab Jamun',
            'Hot Potato',
            'Meatless Meatballs',
            'Mixed Nuts',
            'Moon Pearl',
            'Olives',
            'Orange',
            'Plastic',
            'Red Hard Candy',
            'Rice',
            'Rock',
            'Sand-covered... Something',
            'Silica Grounds',
            'Sweet Beet',
            'Tiny Black Hole',
            'Yellow Hard Candy',
        ]));

        // Create activity log
        $activityLog = PetActivityLogFactory::createUnreadLog(
            $this->em, 
            $pet, 
            ActivityHelpers::PetName($pet) . ' broke open their Green Sportsball Ball and found ' . $revealedItem->getNameWithArticle() . ' inside! (Sportsball is so confusing...)'
        );

        // Award the revealed item
        $this->inventoryService->petCollectsItem($revealedItem, $pet, $pet->getName() . ' found this inside a Green Sportsball Ball!', $activityLog);

        // Award experience
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(15, 25), PetActivityStatEnum::OTHER, null);

        // Destroy the Green Sportsball Ball
        EquipmentFunctions::destroyPetTool($this->em, $pet);

        // Set up the activity log
        $activityLog
            ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [
                PetActivityLogTagEnum::Adventure,
                PetActivityLogTagEnum::Sportsball
            ]))
            ->setChanges($changes->compare($pet))
        ;
        
        return $activityLog;
    }
}