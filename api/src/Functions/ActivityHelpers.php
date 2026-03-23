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

namespace App\Functions;

use App\Entity\Pet;
use App\Entity\User;
use App\Enum\MeritEnum;
use App\Enum\StatusEffectEnum;
use App\Model\ComputedPetSkills;

final class ActivityHelpers
{
    public static function SourceOfLight(ComputedPetSkills $petWithSkills): string
    {
        if($petWithSkills->getPet()->hasMerit(MeritEnum::DARKVISION))
            return 'Darkvision';

        if($petWithSkills->getPet()->getTool() && $petWithSkills->getPet()->getTool()->providesLight())
            return $petWithSkills->getPet()->getTool()->getFullItemName();

        throw new \Exception('No light source found! (Bad game logic!)');
    }

    public static function SourceOfHeatProtection(ComputedPetSkills $petWithSkills): string
    {
        if($petWithSkills->getPet()->hasStatusEffect(StatusEffectEnum::HeatResistant))
            return 'heat-resistance';

        if($petWithSkills->getPet()->getTool() && $petWithSkills->getPet()->getTool()->protectsFromHeat())
            return $petWithSkills->getPet()->getTool()->getItem()->getName();

        throw new \Exception('No source of heat protection found! (Bad game logic!)');
    }

    public static function PetName(Pet $pet): string
    {
        return '%pet:' . $pet->getId() . '.name%';
    }

    public static function UserName(User $user, bool $capitalize = false): string
    {
        if($capitalize)
            return '%user:' . $user->getId() . '.Name%';
        else
            return '%user:' . $user->getId() . '.name%';
    }

    public static function UserNamePossessive(User $user, bool $capitalize = false): string
    {
        if($capitalize)
            return '%user:' . $user->getId() . '.Name\'s%';
        else
            return '%user:' . $user->getId() . '.name\'s%';
    }
}