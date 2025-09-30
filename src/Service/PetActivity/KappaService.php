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


namespace App\Service\PetActivity;

use App\Entity\PetActivityLog;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetBadgeEnum;
use App\Enum\PetSkillEnum;
use App\Enum\UserStat;
use App\Exceptions\PSPNotFoundException;
use App\Functions\ActivityHelpers;
use App\Functions\EquipmentFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Functions\PlayerLogFactory;
use App\Functions\UserFunctions;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;

class KappaService
{
    public function __construct(
        private readonly IRandom $rng,
        private readonly PetExperienceService $petExperienceService,
        private readonly InventoryService $inventoryService,
        private readonly EntityManagerInterface $em,
        private readonly UserStatsService $userStatsRepository
    )
    {
    }

    public function doHuntKappa(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $changes = new PetChanges($pet);

        $totalSkill =
            $petWithSkills->getBrawl(false)->getTotal() +
            $petWithSkills->getStrength()->getTotal() +
            $petWithSkills->getDexterity()->getTotal();

        EquipmentFunctions::destroyPetTool($this->em, $pet);

        if($totalSkill >= 12)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, 'While ' . ActivityHelpers::PetName($pet) . ' was thinking about what to do, a Kappa jumped them! ' . ActivityHelpers::PetName($pet) . ' saw it coming a mile away, though, beat the creature back, and reclaimed its stolen Shirikodama. (Their Cucumber was reduced to a pulp in the process.)')
                ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Fighting', PetActivityLogTagEnum::Adventure ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::HUNT, true);

            $this->inventoryService->petCollectsItem('Shirikodama', $pet, $pet->getName() . ' reclaimed this from a Kappa.', $activityLog);
        }
        else if($this->rng->rngNextInt(1, 20 + $totalSkill) >= 16)
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, 'While ' . ActivityHelpers::PetName($pet) . ' was thinking about what to do, a Kappa jumped them! It was a tough fight, but ' . ActivityHelpers::PetName($pet) . ' beat the creature back, and reclaimed its stolen Shirikodama! (Their Cucumber was reduced to a pulp in the process.)')
                ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Fighting', PetActivityLogTagEnum::Adventure ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);

            $this->inventoryService->petCollectsItem('Shirikodama', $pet, $pet->getName() . ' reclaimed this from a Kappa.', $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, 'While ' . ActivityHelpers::PetName($pet) . ' was thinking about what to do, a Kappa jumped them! It was a tough fight, which ended when the Kappa ate ' . ActivityHelpers::PetName($pet) . '\'s Cucumber, and ran off giggling! >:(')
                ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Fighting', PetActivityLogTagEnum::Adventure ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
        }

        $activityLog->setChanges($changes->compare($pet));

        return $activityLog;
    }

    public function doReturnShirikodama(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $changes = new PetChanges($pet);

        $skills =
            $petWithSkills->getPerception()->getTotal() +
            $petWithSkills->getIntelligence()->getTotal() +
            $petWithSkills->getPet()->getExtroverted() * 3;

        if($skills >= 5)
        {
            $owner = UserFunctions::findOneRecentlyActive($this->em, $pet->getOwner(), 72);

            if(!$owner)
                throw new PSPNotFoundException('Hm... there\'s no one to return it to! (I guess no one\'s been playing Poppy Seed Pets...)');

            EquipmentFunctions::destroyPetTool($this->em, $pet);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' recognized the Shirikodama as belonging to ' . ActivityHelpers::UserName($owner) . ', so returned it to them. ' . ActivityHelpers::UserName($owner) . ' thanked ' . ActivityHelpers::PetName($pet) . ' with many pets and pats.')
                ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Adventure ]))
            ;
            $pet->increaseLove(4)->increaseEsteem(4);
            $this->petExperienceService->gainAffection($pet, 2);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(15, 30), PetActivityStatEnum::OTHER, null);
            PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::ReturnedAShirikodama, $activityLog);

            $this->userStatsRepository->incrementStat($owner, UserStat::PettedAPet, 1);

            PlayerLogFactory::create($this->em, $owner, ActivityHelpers::PetName($pet) . ' returned your Shirikodama! (Some Kappa must have stolen it!) You thank ' . ActivityHelpers::PetName($pet) . ' with pets and pats before swallowing the Shirikodama.', [
                'Shirikodama',
            ]);

            $this->userStatsRepository->incrementStat($pet->getOwner(), 'Returned a Shirikodama', 1);
        }
        else if($this->rng->rngNextInt(1, 3) > 1)
        {
            $owner = UserFunctions::findOneRecentlyActive($this->em, $pet->getOwner(), 72);

            if(!$owner)
                throw new PSPNotFoundException('Hm... there\'s no one to return it to! (I guess no one\'s been playing Poppy Seed Pets...)');

            EquipmentFunctions::destroyPetTool($this->em, $pet);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' wasn\'t immediately sure who the Shirikodama belonged to, so wandered the town for a little before spotting ' . ActivityHelpers::UserName($owner) . ', and recognizing them as the owner! ' . ActivityHelpers::PetName($pet) . ' returned the Shirikodama to ' . ActivityHelpers::UserName($owner) . ', who thanked them with many pets and pats.')
                ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Adventure ]))
            ;
            $pet->increaseLove(4)->increaseEsteem(4);
            $this->petExperienceService->gainAffection($pet, 2);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);
            PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::ReturnedAShirikodama, $activityLog);

            $this->userStatsRepository->incrementStat($owner, UserStat::PettedAPet, 1);

            PlayerLogFactory::create($this->em, $owner, ActivityHelpers::PetName($pet) . ' returned your Shirikodama! (Some Kappa must have stolen it!) You thank ' . ActivityHelpers::PetName($pet) . ' with pets and pats before swallowing the Shirikodama.', [
                'Shirikodama',
            ]);

            $this->userStatsRepository->incrementStat($pet->getOwner(), 'Returned a Shirikodama', 1);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' wasn\'t sure who the Shirikodama belonged to, so wandered the town for a little. They approached several residents, but none were the owner.')
                ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Adventure ]))
            ;
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);
        }

        $activityLog->setChanges($changes->compare($pet));

        return $activityLog;
    }
}