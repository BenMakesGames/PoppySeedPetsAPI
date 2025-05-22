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

use App\Entity\Pet;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetBadgeEnum;
use App\Enum\PetSkillEnum;
use App\Enum\UserStatEnum;
use App\Functions\ActivityHelpers;
use App\Functions\MeritRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;

class SagaSagaService
{
    public function __construct(
        private readonly IRandom $rng,
        private readonly InventoryService $inventoryService,
        private readonly EntityManagerInterface $em,
        private readonly ResponseService $responseService,
        private readonly UserStatsService $userStatsRepository
    )
    {
    }

    public function petCompletesSagaSaga(Pet $pet): bool
    {
        if(!$pet->hasMerit(MeritEnum::SAGA_SAGA))
            return false;

        $possibleSkills = [];

        if($pet->getSkills()->getStealth() >= 5) $possibleSkills[] = PetSkillEnum::STEALTH;
        if($pet->getSkills()->getNature() >= 5) $possibleSkills[] = PetSkillEnum::NATURE;
        if($pet->getSkills()->getBrawl() >= 5) $possibleSkills[] = PetSkillEnum::BRAWL;
        if($pet->getSkills()->getArcana() >= 5) $possibleSkills[] = PetSkillEnum::ARCANA;
        if($pet->getSkills()->getCrafts() >= 5) $possibleSkills[] = PetSkillEnum::CRAFTS;
        if($pet->getSkills()->getMusic() >= 5) $possibleSkills[] = PetSkillEnum::MUSIC;
        if($pet->getSkills()->getScience() >= 5) $possibleSkills[] = PetSkillEnum::SCIENCE;

        if(count($possibleSkills) === 0)
            return false;

        $skill = $this->rng->rngNextFromArray($possibleSkills);

        $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' got 5 points in ' . $skill . ', and was transformed into a skill scroll! All that remains is their ghost...')
            ->addInterestingness(PetActivityLogInterestingness::OneTimeQuestActivity)
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Level-up' ]))
        ;

        $this->inventoryService->petCollectsItem('Skill Scroll: ' . $skill, $pet, $pet->getName() . ' was transformed into this scroll!', $log);

        PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::PRODUCED_A_SKILL_SCROLL, $log);

        $pet
            ->removeMerit(MeritRepository::findOneByName($this->em, MeritEnum::SAGA_SAGA))
            ->removeMerit(MeritRepository::findOneByName($this->em, MeritEnum::AFFECTIONLESS))
            ->addMerit(MeritRepository::findOneByName($this->em, MeritEnum::SPECTRAL))
            ->setName('Ghost of ' . $pet->getName())
            ->resetAllNeeds()
            ->clearExp()
        ;

        $pet->getSkills()
            ->setStealth(0)
            ->setNature(0)
            ->setBrawl(0)
            ->setArcana(0)
            ->setCrafts(0)
            ->setMusic(0)
            ->setScience(0)
        ;

        $pet->getHouseTime()
            ->setSocialEnergy(0)
            ->setActivityTime(0)
        ;

        $this->responseService->setReloadPets();

        $this->userStatsRepository->incrementStat($pet->getOwner(), UserStatEnum::COMPLETED_A_SAGA_SAGA);

        return true;
    }
}