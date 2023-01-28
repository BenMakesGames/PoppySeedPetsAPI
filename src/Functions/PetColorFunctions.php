<?php
namespace App\Functions;

use App\Entity\Pet;
use App\Service\IRandom;

class PetColorFunctions
{
    /**
     * Favors value in 50-100%
     */
    public static function randomSaturation(IRandom $rng): float
    {
        return $rng->rngNextInt($rng->rngNextInt(0, 500), 1000) / 1000.0;
    }

    /**
     * Favors values closer to 50%
     */
    public static function randomLuminosity(IRandom $rng): float
    {
        return $rng->rngNextInt($rng->rngNextInt(0, 500), $rng->rngNextInt(750, 1000)) / 1000.0;
    }

    public static function randomizeColorDistinctFromPreviousColor(IRandom $rng, string $oldColor): string
    {
        $oldRGB = ColorFunctions::Hex2RGB($oldColor);
        $oldHSL = ColorFunctions::RGB2HSL($oldRGB['r'], $oldRGB['g'], $oldRGB['b']);

        $h = $oldHSL['h'] + $rng->rngNextInt(200, 800) / 1000.0;
        if($h > 1) $h -= 1;

        // now pick a random saturation and luminosity within that:
        $s = PetColorFunctions::randomSaturation($rng);
        $l = PetColorFunctions::randomLuminosity($rng);

        return ColorFunctions::HSL2Hex($h, $s, $l);
    }

    public static function generateColorFromParentColors(IRandom $rng, string $parent1Color, string $parent2Color): string
    {
        if($rng->rngNextInt(1, 5) === 1)
        {
            return ColorFunctions::HSL2Hex(
                $rng->rngNextInt(0, 1000) / 1000,
                PetColorFunctions::randomSaturation($rng),
                PetColorFunctions::randomLuminosity($rng)
            );
        }
        else
        {
            // pick a color somewhere between color1 and color2, tending to prefer a 50/50 mix
            $skew = $rng->rngNextInt($rng->rngNextInt(0, 127), $rng->rngNextInt(128, 255));

            $rgb1 = ColorFunctions::Hex2RGB($parent1Color);
            $rgb2 = ColorFunctions::Hex2RGB($parent2Color);

            $r = (int)(($rgb1['r'] * $skew + $rgb2['r'] * (255 - $skew)) / 255);
            $g = (int)(($rgb1['g'] * $skew + $rgb2['g'] * (255 - $skew)) / 255);
            $b = (int)(($rgb1['b'] * $skew + $rgb2['b'] * (255 - $skew)) / 255);

            // jiggle the final values a little:
            $r = NumberFunctions::clamp($r + $rng->rngNextInt(-6, 6), 0, 255);
            $g = NumberFunctions::clamp($g + $rng->rngNextInt(-6, 6), 0, 255);
            $b = NumberFunctions::clamp($b + $rng->rngNextInt(-6, 6), 0, 255);

            return ColorFunctions::RGB2Hex($r, $g, $b);
        }
    }

    public static function recolorPet(IRandom $rng, Pet $pet, float $maxSaturation = 1)
    {
        $colors = PetColorFunctions::generateRandomPetColors($rng, $maxSaturation);

        $pet
            ->setColorA($colors[0])
            ->setColorB($colors[1])
        ;
    }

    public static function generateRandomPetColors(IRandom $rng, $maxSaturation = 1): array
    {
        $h = $rng->rngNextInt(0, 1000) / 1000.0;
        $s = PetColorFunctions::randomSaturation($rng) * $maxSaturation;
        $l = PetColorFunctions::randomLuminosity($rng);

        $strategy = $rng->rngNextInt(1, 100);

        $h2 = $h;
        $s2 = $s;
        $l2 = $l;

        if($strategy <= 35)
        {
            // complementary color
            $h2 = $h2 + 0.5;
            if($h2 > 1) $h2 -= 1;

            if($rng->rngNextInt(1, 2) === 1)
            {
                if($s < $maxSaturation / 2)
                    $s2 = $s * 2;
                else
                    $s2 = $s / 2;
            }
        }
        else if($strategy <= 70)
        {
            // different luminosity
            if($l2 <= 0.5)
                $l2 += 0.5;
            else
                $l2 -= 0.5;
        }
        else if($strategy <= 90)
        {
            // black & white
            if($l < 0.3333)
                $l2 = $rng->rngNextInt(850, 1000) / 1000.0;
            else if($l > 0.6666)
                $l2 = $rng->rngNextInt(0, 150) / 1000.0;
            else if($rng->rngNextInt(1, 2) === 1)
                $l2 = $rng->rngNextInt(850, 1000) / 1000.0;
            else
                $l2 = $rng->rngNextInt(0, 150) / 1000.0;
        }
        else
        {
            // RANDOM!
            $h2 = $rng->rngNextInt(0, 1000) / 1000.0;
            $s2 = PetColorFunctions::randomSaturation($rng) * $maxSaturation;
            $l2 = PetColorFunctions::randomSaturation($rng);
        }

        return [
            ColorFunctions::HSL2Hex($h, $s, $l),
            ColorFunctions::HSL2Hex($h2, $s2, $l2)
        ];
    }
}
