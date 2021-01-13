<?php
namespace App\Service;

use App\Entity\Pet;
use App\Functions\ColorFunctions;
use App\Functions\NumberFunctions;

class PetColorService
{
    /**
     * Favors value in 50-100%
     */
    public function randomSaturation(): float
    {
        return mt_rand(mt_rand(0, 500), 1000) / 1000.0;
    }

    /**
     * Favors values closer to 50%
     */
    public function randomLuminosity(): float
    {
        return mt_rand(mt_rand(0, 500), mt_rand(750, 1000)) / 1000.0;
    }

    public function randomizeColorDistinctFromPreviousColor(string $oldColor)
    {
        $oldRGB = ColorFunctions::Hex2RGB($oldColor);
        $oldHSL = ColorFunctions::RGB2HSL($oldRGB['r'], $oldRGB['g'], $oldRGB['b']);

        $h = $oldHSL['h'] + mt_rand(200, 800) / 1000.0;
        if($h > 1) $h -= 1;

        // now pick a random saturation and luminosity within that:
        $s = $this->randomSaturation();
        $l = $this->randomLuminosity();

        return ColorFunctions::HSL2Hex($h, $s, $l);
    }

    public function generateColorFromParentColors(string $parent1Color, string $parent2Color): string
    {
        if(mt_rand(1, 5) === 1)
        {
            return ColorFunctions::HSL2Hex(
                mt_rand(0, 1000) / 1000,
                $this->randomSaturation(),
                $this->randomLuminosity()
            );
        }
        else
        {
            // pick a color somewhere between color1 and color2, tending to prefer a 50/50 mix
            $skew = mt_rand(mt_rand(0, 127), mt_rand(128, 255));

            $rgb1 = ColorFunctions::Hex2RGB($parent1Color);
            $rgb2 = ColorFunctions::Hex2RGB($parent2Color);

            $r = (int)(($rgb1['r'] * $skew + $rgb2['r'] * (255 - $skew)) / 255);
            $g = (int)(($rgb1['g'] * $skew + $rgb2['g'] * (255 - $skew)) / 255);
            $b = (int)(($rgb1['b'] * $skew + $rgb2['b'] * (255 - $skew)) / 255);

            // jiggle the final values a little:
            $r = NumberFunctions::clamp($r + mt_rand(-6, 6), 0, 255);
            $g = NumberFunctions::clamp($g + mt_rand(-6, 6), 0, 255);
            $b = NumberFunctions::clamp($b + mt_rand(-6, 6), 0, 255);

            return ColorFunctions::RGB2Hex($r, $g, $b);
        }
    }

    public function recolorPet(Pet $pet, float $maxSaturation = 1)
    {
        $colors = $this->generateRandomPetColors($maxSaturation);

        $pet
            ->setColorA($colors[0])
            ->setColorB($colors[1])
        ;
    }

    public function generateRandomPetColors($maxSaturation = 1)
    {
        $h = mt_rand(0, 1000) / 1000.0;
        $s = $this->randomSaturation() * $maxSaturation;
        $l = $this->randomLuminosity();

        $strategy = mt_rand(1, 100);

        $h2 = $h;
        $s2 = $s;
        $l2 = $l;

        if($strategy <= 35)
        {
            // complementary color
            $h2 = $h2 + 0.5;
            if($h2 > 1) $h2 -= 1;

            if(mt_rand(1, 2) === 1)
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
                $l2 = mt_rand(850, 1000) / 1000.0;
            else if($l > 0.6666)
                $l2 = mt_rand(0, 150) / 1000.0;
            else if(mt_rand(1, 2) === 1)
                $l2 = mt_rand(850, 1000) / 1000.0;
            else
                $l2 = mt_rand(0, 150) / 1000.0;
        }
        else
        {
            // RANDOM!
            $h2 = mt_rand(0, 1000) / 1000.0;
            $s2 = $this->randomSaturation() * $maxSaturation;
            $l2 = $this->randomSaturation();
        }

        return [
            ColorFunctions::HSL2Hex($h, $s, $l),
            ColorFunctions::HSL2Hex($h2, $s2, $l2)
        ];
    }
}
