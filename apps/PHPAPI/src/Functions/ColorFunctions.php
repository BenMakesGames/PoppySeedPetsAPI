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

use App\Entity\Pet;

final class ColorFunctions
{
    public static function RGB2HSL(int $red, int $green, int $blue)
    {
        $r = $red / 255;
        $g = $green / 255;
        $b = $blue / 255;

        $min = min($r, $g, $b);
        $max = max($r, $g, $b);

        $l = ($max + $min) / 2;

        if($red === $green && $green == $blue)
        {
            $h = 0;
            $s = 0;
        }
        else
        {
            $range = $max - $min;

            if($l < 0.5)
                $s = $range / ($max + $min);
            else
                $s = $range / (2 - $max - $min);

            $dR = (($max - $r) / 6 + ($range / 2)) / $range;
            $dG = (($max - $g) / 6 + ($range / 2)) / $range;
            $dB = (($max - $b) / 6 + ($range / 2)) / $range;

            if($red >= $green && $red >= $blue)
                $h = $dB - $dG;
            else if($green >= $blue)
                $h = 1 / 3 + $dR - $dB;
            else
                $h = 2 / 3 + $dG - $dR;

            if($h < 0)
                $h += 1;
            else if($h > 1)
                $h -= 1;
        }

        return [ 'h' => $h, 's' => $s, 'l' => $l ];
    }

    public static function HSL2RGB(float $h, float $s, float $l)
    {
        $r = $l;
        $g = $l;
        $b = $l;
        $v = ($l <= 0.5) ? ($l * (1.0 + $s)) : ($l + $s - $l * $s);
        if ($v > 0){
            $m = $l + $l - $v;
            $sv = ($v - $m ) / $v;
            $h *= 6.0;
            $sextant = (int)floor($h);
            $fract = $h - $sextant;
            $vsf = $v * $sv * $fract;
            $mid1 = $m + $vsf;
            $mid2 = $v - $vsf;

            switch ($sextant)
            {
                case 0:
                    $r = $v;
                    $g = $mid1;
                    $b = $m;
                    break;
                case 1:
                    $r = $mid2;
                    $g = $v;
                    $b = $m;
                    break;
                case 2:
                    $r = $m;
                    $g = $v;
                    $b = $mid1;
                    break;
                case 3:
                    $r = $m;
                    $g = $mid2;
                    $b = $v;
                    break;
                case 4:
                    $r = $mid1;
                    $g = $m;
                    $b = $v;
                    break;
                case 5:
                    $r = $v;
                    $g = $m;
                    $b = $mid2;
                    break;
            }
        }
        return [ 'r' => $r * 255, 'g' => $g * 255, 'b' => $b * 255 ];
    }

    public static function RGB2Hex(int $r, int $g, int $b): string
    {
        return \str_pad(\dechex(($r << 16) + ($g << 8) + $b), 6, '0', STR_PAD_LEFT);
    }

    public static function Hex2RGB(string $color): array
    {
        $dec = hexdec($color);

        return [
            'r' => $dec >> 16,
            'g' => ($dec >> 8) % 256,
            'b' => $dec % 256
        ];
    }

    public static function HSL2Hex(float $h, float $s, float $l): string
    {
        $rgb = self::HSL2RGB($h, $s, $l);

        return ColorFunctions::RGB2Hex((int)$rgb['r'], (int)$rgb['g'], (int)$rgb['b']);
    }

    public static function Hex2HSL(string $hexColor)
    {
        $rgb = ColorFunctions::Hex2RGB($hexColor);
        return ColorFunctions::RGB2HSL($rgb['r'], $rgb['g'], $rgb['b']);
    }

    public static function ChangeHue(string $hexColor, float $hue)
    {
        $hsl = ColorFunctions::Hex2HSL($hexColor);
        $hsl['h'] = $hue;
        return ColorFunctions::HSL2Hex($hsl['h'], $hsl['s'], $hsl['l']);
    }

    // not-perfect, but should be computationally fast!
    public static function GrayscalifyHex(string $hexColor)
    {
        $r = substr($hexColor, 0, 2);
        $g = substr($hexColor, 2, 2);
        $b = substr($hexColor, 4, 2);

        if($g >= $r && $g >= $b)
            return $g . $g . $g;
        else if($r >= $g && $r >= $b)
            return $r . $r . $r;
        else
            return $b . $b . $b;
    }
}
