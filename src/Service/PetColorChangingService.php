<?php
namespace App\Service;

use App\Functions\ColorFunctions;

class PetColorChangingService
{
    public function RandomizeColorDistinctFromPreviousColor($oldColor)
    {

        $oldRGB = ColorFunctions::Hex2RGB($oldColor);
        $oldHSL = ColorFunctions::RGB2HSL($oldRGB['r'], $oldRGB['g'], $oldRGB['b']);

        $h = $oldHSL['h'] + mt_rand(200, 800) / 1000.0;
        if($h > 1) $h -= 1;

        // now pick a random saturation and luminosity within that:
        $s = mt_rand(mt_rand(0, 500), 1000) / 1000.0;
        $l = mt_rand(mt_rand(0, 500), mt_rand(750, 1000)) / 1000.0;

        return ColorFunctions::HSL2Hex($h, $s, $l);
    }
}
