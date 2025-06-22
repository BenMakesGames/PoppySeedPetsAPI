<?php
declare(strict_types = 1);

namespace App\Service\PetActivity;

use App\Entity\PetActivityLog;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ActivityHelpers;
use App\Functions\EquipmentFunctions;
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
}