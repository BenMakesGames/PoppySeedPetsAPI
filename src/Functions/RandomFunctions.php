<?php
declare(strict_types=1);

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
