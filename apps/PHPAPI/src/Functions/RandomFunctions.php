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

class RandomFunctions
{
    private const SQUIRREL_3_BIT_NOISE_1 = 0x68e31da4;
    private const SQUIRREL_3_BIT_NOISE_2 = 0xb5297a4d;
    private const SQUIRREL_3_BIT_NOISE_3 = 0x1b56c4e9;

    public static function squirrel3Noise(int $position, int $seed): int
    {
        $mangled = ($position * self::SQUIRREL_3_BIT_NOISE_1) & 0xffffffff;
        $mangled = ($mangled + $seed) & 0xffffffff;
        $mangled ^= ($mangled >> 8);
        $mangled = ($mangled + self::SQUIRREL_3_BIT_NOISE_2) & 0xffffffff;
        $mangled ^= ($mangled << 8) & 0xffffffff;
        $mangled = ($mangled * self::SQUIRREL_3_BIT_NOISE_3) & 0xffffffff;
        $mangled ^= ($mangled >> 8);

        return $mangled % 0xffffffff;
    }
}
