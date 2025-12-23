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

namespace App\Model;

use Symfony\Component\Serializer\Attribute\Groups;

class PetChangesSummary
{
    #[Groups(["petActivityLogs", "petActivityLogAndPublicPet"])]
    public ?string $food = null;

    #[Groups(["petActivityLogs", "petActivityLogAndPublicPet"])]
    public ?string $safety = null;

    #[Groups(["petActivityLogs", "petActivityLogAndPublicPet"])]
    public ?string $love = null;

    #[Groups(["petActivityLogs", "petActivityLogAndPublicPet"])]
    public ?string $esteem = null;

    #[Groups(["petActivityLogs", "petActivityLogAndPublicPet"])]
    public ?string $exp = null;

    #[Groups(["petActivityLogs", "petActivityLogAndPublicPet"])]
    public ?string $level = null;

    #[Groups(["petActivityLogs", "petActivityLogAndPublicPet"])]
    public ?string $affection = null;

    #[Groups(["petActivityLogs", "petActivityLogAndPublicPet"])]
    public ?string $affectionLevel = null;

    #[Groups(["petActivityLogs", "petActivityLogAndPublicPet"])]
    public ?string $scrollLevel = null;

    public function containsLevelUp(): bool
    {
        return $this->level !== null && str_contains($this->level, '+');
    }

    public static function rate(int $value): ?string
    {
        if($value > 20)
            return '++++';
        else if($value > 10)
            return '+++';
        else if($value > 4)
            return '++';
        else if($value > 0)
            return '+';
        else if($value < -20)
            return '----';
        else if($value < -10)
            return '---';
        else if($value < -4)
            return '--';
        else if($value < 0)
            return '-';
        else
            return null;
    }
}
