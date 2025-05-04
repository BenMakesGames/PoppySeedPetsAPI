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
use App\Enum\MeritEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetBadgeEnum;
use App\Enum\PetSkillEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Functions\StatusEffectHelpers;
use App\Model\ComputedPetSkills;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

class WerecreatureEncounterService
{
    public function __construct(
        private readonly PetExperienceService $petExperienceService,
        private readonly IRandom $rng,
        private readonly ResponseService $responseService,
        private readonly InventoryService $inventoryService,
        private readonly EntityManagerInterface $em
    )
    {
    }

    public function encounterWerecreature(ComputedPetSkills $petWithSkills, string $doingWhat, array $tags): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $message = 'Under the influence of the full moon, a werecreature leapt out and attacked %pet:' . $pet->getId() . '.name% while they were out ' . $doingWhat . '! ';

        $hat = $pet->getHat();

        if($hat)
        {
            $treasure = $hat->getItem()->getTreasure();

            if($treasure && $treasure->getSilver() > 0)
            {
                $lootItem = ItemRepository::findOneByName($this->em, $this->rng->rngNextFromArray([
                    'Talon', 'Fluff'
                ]));

                $pet
                    ->increaseEsteem($this->rng->rngNextInt(2, 4))
                    ->increaseSafety($this->rng->rngNextInt(2, 4))
                ;

                $message .= 'However, upon seeing %pet:' . $pet->getId() . '.name%\'s silver ' . $hat->getItem()->getName() . ', the creature ran off, dropping ' . $lootItem->getNameWithArticle() . ' as it went!';

                $activityLog = $this->responseService->createActivityLog($pet, $message, '')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, array_merge($tags, [ 'Werecreature', 'Fighting' ])))
                ;

                PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::DEFEATED_A_WERECREATURE_WITH_SILVER, $activityLog);

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::ARCANA ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::HUNT, true);

                $this->inventoryService->petCollectsItem($lootItem, $pet, $pet->getName() . ' scared off a werecreature, and received this.', $activityLog);

                return $activityLog;
            }
        }

        $tool = $pet->getTool();

        if($tool)
        {
            $treasure = $tool->getItem()->getTreasure();

            if($treasure && $treasure->getSilver() > 0)
            {
                $lootItem = ItemRepository::findOneByName($this->em, $this->rng->rngNextFromArray([
                    'Talon', 'Fluff'
                ]));

                $pet
                    ->increaseEsteem($this->rng->rngNextInt(2, 4))
                    ->increaseSafety($this->rng->rngNextInt(2, 4))
                ;

                $message .= '%pet:' . $pet->getId() . '.name% brandished their silver ' . $tool->getItem()->getName() . '; the creature ran off at the sight of it, dropping ' . $lootItem->getNameWithArticle() . ' as it went!';

                $activityLog = $this->responseService->createActivityLog($pet, $message, '')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, array_merge($tags, [ 'Werecreature', 'Fighting' ])))
                ;

                PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::DEFEATED_A_WERECREATURE_WITH_SILVER, $activityLog);

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::ARCANA ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::HUNT, true);

                $this->inventoryService->petCollectsItem($lootItem, $pet, $pet->getName() . ' chased off a werecreature, and received this.', $activityLog);

                return $activityLog;
            }
        }

        $skill = 20 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl()->getTotal();

        if($this->rng->rngNextInt(1, $skill) >= 15)
        {
            $lootItem = ItemRepository::findOneByName($this->em, $this->rng->rngNextFromArray([
                'Talon', 'Fluff'
            ]));

            $silverblood = $pet->hasMerit(MeritEnum::SILVERBLOOD);

            StatusEffectHelpers::applyStatusEffect($this->em, $pet, StatusEffectEnum::BITTEN_BY_A_WERECREATURE, 1);

            $pet
                ->increaseEsteem($this->rng->rngNextInt(2, 4))
                ->increaseSafety(-$this->rng->rngNextInt(2, 4))
            ;

            if($silverblood)
                $message .= '%pet:' . $pet->getId() . '.name% beat the creature back, and received ' . $lootItem->getNameWithArticle() . ', but also received a bite during the encounter... (Good thing they\'re a silverblood!)';
            else
                $message .= '%pet:' . $pet->getId() . '.name% beat the creature back, and received ' . $lootItem->getNameWithArticle() . ', but also received a bite during the encounter... (Uh oh...)';

            $activityLog = $this->responseService->createActivityLog($pet, $message, '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, array_merge($tags, [ 'Werecreature', 'Fighting' ])))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::HUNT, true);

            $this->inventoryService->petCollectsItem($lootItem, $pet, $pet->getName() . ' received this from a fight with a werecreature.', $activityLog);

            return $activityLog;
        }
        else
        {
            $pet
                ->increaseEsteem(-$this->rng->rngNextInt(2, 4))
                ->increaseSafety(-$this->rng->rngNextInt(4, 8))
            ;

            StatusEffectHelpers::applyStatusEffect($this->em, $pet, StatusEffectEnum::BITTEN_BY_A_WERECREATURE, 1);

            $message .= '%pet:' . $pet->getId() . '.name% eventually escaped the creature, but not before being scratched and bitten! (Uh oh!)';

            $activityLog = $this->responseService->createActivityLog($pet, $message, '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, array_merge($tags, [ 'Werecreature', 'Fighting' ])))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(60, 120), PetActivityStatEnum::HUNT, true);

            return $activityLog;
        }
    }
}