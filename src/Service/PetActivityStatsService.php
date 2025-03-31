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


namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetActivityStats;
use App\Enum\EnumInvalidValueException;
use App\Enum\PetActivityStatEnum;
use Doctrine\ORM\EntityManagerInterface;

class PetActivityStatsService
{
    public const STATS_THAT_CANT_FAIL = [
        PetActivityStatEnum::PARK_EVENT,
        PetActivityStatEnum::OTHER
    ];

    public const STAT_COLORS = [
        PetActivityStatEnum::UMBRA => '#9900FF', // purple
        PetActivityStatEnum::SMITH => '#FFCC00', // yellow
        PetActivityStatEnum::PLASTIC_PRINT => '#FFFFFF', // white
        PetActivityStatEnum::CRAFT => '#FF6600', // orange
        PetActivityStatEnum::MAGIC_BIND => '#FF00FF', // magenta
        PetActivityStatEnum::GATHER => '#33CC00', // green
        PetActivityStatEnum::PROTOCOL_7 => '#336600', // dark green
        PetActivityStatEnum::PROGRAM => '#000000', // black
        PetActivityStatEnum::HUNT => '#CC0000', // red
        PetActivityStatEnum::FISH => '#3399FF', // blue
        PetActivityStatEnum::PARK_EVENT => '#996600', // brown
        PetActivityStatEnum::OTHER => '#999999', // gray
    ];

    public const STAT_LABELS = [
        PetActivityStatEnum::UMBRA => 'Umbra',
        PetActivityStatEnum::SMITH => 'Smithing',
        PetActivityStatEnum::PLASTIC_PRINT => '3D Printer',
        PetActivityStatEnum::CRAFT => 'Crafting',
        PetActivityStatEnum::MAGIC_BIND => 'Magic-binding',
        PetActivityStatEnum::GATHER => 'Gathering',
        PetActivityStatEnum::PROTOCOL_7 => 'Protocol 7',
        PetActivityStatEnum::PROGRAM => 'Programming',
        PetActivityStatEnum::HUNT => 'Hunting',
        PetActivityStatEnum::FISH => 'Fishing',
        PetActivityStatEnum::PARK_EVENT => 'Park Event',
        PetActivityStatEnum::OTHER => 'Other',
    ];

    /**
     * @throws EnumInvalidValueException
     */
    public static function logStat(EntityManagerInterface $em, Pet $pet, string $stat, ?bool $success, int $time)
    {
        $stat = strtolower($stat);

        if(!PetActivityStatEnum::isAValue($stat))
            throw new EnumInvalidValueException(PetActivityStatEnum::class, $stat);

        $canFail = !in_array($stat, self::STATS_THAT_CANT_FAIL);

        if($canFail)
        {
            if($success === null)
                throw new \InvalidArgumentException('$success must be true or false for ' . $stat . ' events.');

            $countSetter = 'increase' . $stat . ($success ? 'success' : 'failure');
        }
        else
            $countSetter = 'increase' . $stat;

        $timeSetter = 'increase' . $stat . 'time';

        if($pet->getPetActivityStats() === null)
        {
            $petActivityStats = new PetActivityStats();

            $pet->setPetActivityStats($petActivityStats);

            $em->persist($petActivityStats);
        }

        $pet->getPetActivityStats()
            ->{$countSetter}()
            ->{$timeSetter}($time)
        ;
    }
}
