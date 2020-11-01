<?php
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
            $sextant = floor($h);
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

    public static function tweakColor(string $color, int $radius = 12): string
    {
        $newColor = '';

        for($i = 0; $i < 3; $i++)
        {
            $part = hexdec($color[$i * 2] . $color[$i * 2 + 1]);    // get color part as decimal
            $part += mt_rand(-$radius, $radius);                    // randomize
            $part = max(0, min(255, $part));                        // keep between 0 and 255
            $part = str_pad(dechex($part), 2, '0', STR_PAD_LEFT);   // turn back into hex

            $newColor .= $part;
        }

        return $newColor;
    }

    public static function generateRandomPetColors($maxSaturation = 1)
    {
        $h = mt_rand(0, 1000) / 1000.0;
        $s = mt_rand(mt_rand(0, 500), 1000) / 1000.0 * $maxSaturation;
        $l = mt_rand(mt_rand(0, 500), mt_rand(750, 1000)) / 1000.0;

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
            $s2 = mt_rand(mt_rand(0, 500), 1000) / 1000.0 * $maxSaturation;
            $l2 = mt_rand(mt_rand(0, 500), mt_rand(750, 1000)) / 1000.0;
        }

        return [
            ColorFunctions::HSL2Hex($h, $s, $l),
            ColorFunctions::HSL2Hex($h2, $s2, $l2)
        ];
    }

    // not-perfect, but should be computationally fast!
    public function GrayscalifyHex(string $hexColor)
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
