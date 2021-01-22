<?php
namespace App\Functions;

class RandomFunctions
{
    private const SQUIRREL_3_BIT_NOISE_1 = 0x68e31da4;
    private const SQUIRREL_3_BIT_NOISE_2 = 0xb5297a4d;
    private const SQUIRREL_3_BIT_NOISE_3 = 0x1b56c4e9;

    public static function squirrel3Noise(int $position, int $seed)
    {
        $mangled = $position;

        $mangled *= self::SQUIRREL_3_BIT_NOISE_1;
        $mangled += $seed;
        $mangled ^= ($mangled >> 8);
        $mangled += self::SQUIRREL_3_BIT_NOISE_2;
        $mangled ^= ($mangled << 8);
        $mangled *= self::SQUIRREL_3_BIT_NOISE_3;
        $mangled ^= ($mangled >> 8);

        return $mangled % 0xffffffff;
    }
}
