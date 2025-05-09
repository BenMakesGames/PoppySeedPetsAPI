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
use App\Enum\StatusEffectEnum;
use App\Model\ComputedPetSkills;
use App\Service\IRandom;
use App\Service\PetActivity\Daydreams\FoodFightDaydream;
use App\Service\PetActivity\Daydreams\IceCreamDaydream;
use App\Service\PetActivity\Daydreams\NoodleDaydream;
use App\Service\PetActivity\Daydreams\PizzaDaydream;

class DreamingAndDaydreamingService
{
    public function __construct(
        private readonly DreamingService $dreamingService,
        private readonly IceCreamDaydream $iceCreamDaydream,
        private readonly PizzaDaydream $pizzaDaydream,
        private readonly IRandom $rng,
        private readonly FoodFightDaydream $foodFightDaydream,
        private readonly NoodleDaydream $noodleDaydream
    )
    {
    }

    public function maybeDreamOrDaydream(ComputedPetSkills $petWithSkills): bool
    {
        $pet = $petWithSkills->getPet();

        if($this->maybeDreamDueToStatusEffect($petWithSkills))
            return true;

        if($this->toolOrMeritInducedDream($pet, $this->rng))
        {
            $this->dreamingService->dream($pet);
            return true;
        }

        return false;
    }

    private static function toolOrMeritInducedDream(Pet $pet, IRandom $rng): bool
    {
        if($pet->hasMerit(MeritEnum::DREAMWALKER) && $rng->rngNextInt(1, 200) === 1)
            return true;

        if($pet->getTool() && $pet->getTool()->isDreamcatcher() && $rng->rngNextInt(1, 200) === 1)
            return true;

        return false;
    }

    private function maybeDreamDueToStatusEffect(ComputedPetSkills $petWithSkills): bool
    {
        if(!$this->rng->rngNextBool())
            return false;

        $pet = $petWithSkills->getPet();

        if($pet->hasStatusEffect(StatusEffectEnum::ONEIRIC))
        {
            $this->dreamingService->dream($pet);
            $pet->removeStatusEffect($pet->getStatusEffect(StatusEffectEnum::ONEIRIC));
            return true;
        }

        if($pet->hasStatusEffect(StatusEffectEnum::DAYDREAM_ICE_CREAM))
        {
            $this->iceCreamDaydream->doAdventure($petWithSkills);
            $pet->removeStatusEffect($pet->getStatusEffect(StatusEffectEnum::DAYDREAM_ICE_CREAM));
            return true;
        }

        if($pet->hasStatusEffect(StatusEffectEnum::DAYDREAM_PIZZA))
        {
            $this->pizzaDaydream->doAdventure($petWithSkills);
            $pet->removeStatusEffect($pet->getStatusEffect(StatusEffectEnum::DAYDREAM_PIZZA));
            return true;
        }

        if($pet->hasStatusEffect(StatusEffectEnum::DAYDREAM_FOOD_FIGHT))
        {
            $this->foodFightDaydream->doAdventure($petWithSkills);
            $pet->removeStatusEffect($pet->getStatusEffect(StatusEffectEnum::DAYDREAM_FOOD_FIGHT));
            return true;
        }

        if($pet->hasStatusEffect(StatusEffectEnum::DAYDREAM_NOODLES))
        {
            $this->noodleDaydream->doAdventure($petWithSkills);
            $pet->removeStatusEffect($pet->getStatusEffect(StatusEffectEnum::DAYDREAM_NOODLES));
            return true;
        }

        return false;
    }
}