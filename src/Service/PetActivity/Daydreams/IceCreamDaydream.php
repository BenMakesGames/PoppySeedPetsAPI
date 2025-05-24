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
use App\Enum\PetActivityStatEnum;
use App\Enum\PetBadgeEnum;
use App\Enum\PetSkillEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ActivityHelpers;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Functions\StatusEffectHelpers;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class IceCreamDaydream
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
            $this->doCityOfFrozenIceCream(...),
            $this->doMintChocolateSurfing(...),
            $this->doShapeIceCreamLandscapes(...),
            $this->doFlavorfulStalactites(...),
            $this->doGelatoFortuneTeller(...),
        ];

        $log = $this->rng->rngNextFromArray($adventures)($petWithSkills);

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);

        $log
            ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Dream' ]))
            ->setIcon('icons/status-effect/daydream-ice-cream')
            ->addInterestingness(PetActivityLogInterestingness::UncommonActivity)
            ->setChanges($changes->compare($pet));

        return $log;
    }

    private function doCityOfFrozenIceCream(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasMerit(MeritEnum::GOURMAND))
        {
            $log = PetActivityLogFactory::createUnreadLog(
                $this->em,
                $petWithSkills->getPet(),
                ActivityHelpers::PetName($pet) . ' daydreamed about a city sculpted from ever-frozen ice cream where rivers of Neapolitan Ice Cream flowed. They scooped up some of the neapolitan (and ate some, too - a true Gourmand!) before snapping back to reality.');

            $log->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Eating', 'Gourmand' ]));

            $pet->increaseFood(8);
        }
        else
        {
            $log = PetActivityLogFactory::createUnreadLog(
                $this->em,
                $petWithSkills->getPet(),
                ActivityHelpers::PetName($pet) . ' daydreamed about a city sculpted from ever-frozen ice cream where rivers of Neapolitan Ice Cream flowed. They scooped up some of the neapolitan before snapping back to reality.');
        }

        $this->inventoryService->petCollectsItem('Neapolitan Ice Cream', $pet, $pet->getName() . ' found this in a daydream.', $log);

        PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::PULLED_AN_ITEM_FROM_A_DREAM, $log);

        return $log;
    }

    private function doMintChocolateSurfing(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getArcana()->getTotal());

        if($roll >= 15)
        {
            $log = PetActivityLogFactory::createUnreadLog(
                $this->em,
                $petWithSkills->getPet(),
                ActivityHelpers::PetName($pet) . ' daydreamed they were surfing on a wave of mint chocolate ice cream, dodging pieces of cookies as they went. They caught one of the cookies just before it hit their face, then snapped back to reality!');

            $this->inventoryService->petCollectsItem('Mini Chocolate Chip Cookies', $pet, $pet->getName() . ' found this in a daydream.', $log);

            PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::PULLED_AN_ITEM_FROM_A_DREAM, $log);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::Arcana ], $log);

            return $log;
        }
        else
        {
            $log = PetActivityLogFactory::createUnreadLog(
                $this->em,
                $petWithSkills->getPet(),
                ActivityHelpers::PetName($pet) . ' daydreamed they were surfing on a wave of mint chocolate ice cream, dodging pieces of cookies as they went. A whole cookie hit them in the face, jolting them back to reality!');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $log);

            return $log;
        }
    }

    private function doShapeIceCreamLandscapes(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasMerit(MeritEnum::GOURMAND))
        {
            $log = PetActivityLogFactory::createUnreadLog(
                $this->em,
                $petWithSkills->getPet(),
                ActivityHelpers::PetName($pet) . ' daydreamed they were a giant in a world where mountains were scoops of ice cream, shaping new landscapes. They created mountain ranges of rocky road (and ate some, too - a true Gourmand!), then snapped back to reality!');

            $log->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Eating', 'Gourmand' ]));

            $pet->increaseFood(8);
        }
        else
        {
            $log = PetActivityLogFactory::createUnreadLog(
                $this->em,
                $petWithSkills->getPet(),
                ActivityHelpers::PetName($pet) . ' daydreamed they were a giant in a world where mountains were scoops of ice cream, shaping new landscapes. They created mountain ranges of rocky road, then snapped back to reality!');
        }

        $this->petExperienceService->gainExp($pet, 5, [ PetSkillEnum::Crafts ], $log);

        return $log;
    }

    private function doFlavorfulStalactites(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $log = PetActivityLogFactory::createUnreadLog(
            $this->em,
            $petWithSkills->getPet(),
            ActivityHelpers::PetName($pet) . ' daydreamed they discovered crystal caverns where the stalactites dripped flavors unknown to the modern world. They bottled some up before snapping back to reality...');

        $this->inventoryService->petCollectsItem('Mystery Syrup', $pet, $pet->getName() . ' found this in a daydream.', $log);

        PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::PULLED_AN_ITEM_FROM_A_DREAM, $log);

        return $log;
    }

    private function doGelatoFortuneTeller(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $possibleFates = [
            StatusEffectEnum::FATED_DELICIOUSNESS => 'unavoidable deliciousness',
            StatusEffectEnum::FATED_SOAKEDLY => 'a watery grave',
            StatusEffectEnum::FATED_LUNARLY => 'the lunar surface',
        ];

        $fate = $this->rng->rngNextFromArray(array_keys($possibleFates));

        $log = PetActivityLogFactory::createUnreadLog(
            $this->em,
            $petWithSkills->getPet(),
            ActivityHelpers::PetName($pet) . ' daydreamed they met an enchanted ice cream vendor who could foresee the future in swirls of gelato. "I see ' . $possibleFates[$fate] . ' in your future," they said in a raspy voice before ' . ActivityHelpers::PetName($pet) . ' snapped back to reality.'
        );

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::Arcana ], $log);

        StatusEffectHelpers::applyStatusEffect($this->em, $pet, $fate, 1);

        return $log;
    }
}