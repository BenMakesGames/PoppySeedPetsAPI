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

final class NumberFunctions
{
    public static function clamp(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }

    public static function findDivisors(int $number): array
    {
        if($number < 1)
            throw new \InvalidArgumentException('$number must be at least 1.');
        else if($number === 1)
            return [ 1 ];
        else if($number < 4)
            return [ 1, $number ];

        $largestPossible = (int)sqrt($number);

        $divisors = [ 1, $number ];

        for($check = 2; $check <= $largestPossible; $check++)
        {
            if($number % $check === 0)
            {
                $divisors[] = $check;

                $twin = $number / $check;

                if($twin !== $check)
                    $divisors[] = $twin;
            }
        }

        return $divisors;
    }
}
