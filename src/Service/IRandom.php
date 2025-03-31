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

interface IRandom
{
    function __construct(?int $seed = null);
    function rngNext(): int;
    function rngNextFloat(): float;
    function rngNextBool(): bool;
    function rngNextInt(int $min, int $inclusiveMax): int;
    function rngNextFromArray(array $array): mixed;
    function rngNextShuffle(array &$array);
    function rngNextSubsetFromArray(array $array, int $number): array;

    // hmm...
    function rngNextTweakedColor(string $color, int $radius = 12): string;
}