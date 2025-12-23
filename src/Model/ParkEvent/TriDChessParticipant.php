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

namespace App\Model\ParkEvent;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\MeritEnum;

class TriDChessParticipant implements ParkEventParticipant
{
    public Pet $pet;
    public int $skill;
    public bool $isWinner = false;
    public PetActivityLog $activityLog;

    public function __construct(Pet $pet)
    {
        $this->pet = $pet;
        $this->skill = self::getSkill($pet);
    }

    public static function getSkill(Pet $pet): int
    {
        $skill = 1 + $pet->getSkills()->getIntelligence() + $pet->getSkills()->getScience();

        if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            $skill += 2;

        return $skill;
    }

    public function getPet(): Pet
    {
        return $this->pet;
    }

    public function getIsWinner(): bool
    {
        return $this->isWinner;
    }

    public function getActivityLog(): PetActivityLog
    {
        return $this->activityLog;
    }
}
