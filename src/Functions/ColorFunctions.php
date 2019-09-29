<?php
namespace App\Functions;

final class ColorFunctions
{
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
        return [ 'r' => $r * 255.0, 'g' => $g * 255.0, 'b' => $b * 255.0 ];
    }

    public static function RGB2Hex(int $r, int $g, int $b): string
    {
        return \str_pad(\dechex(($r << 16) + ($g << 8) + $b), 6, '0', STR_PAD_LEFT);
    }

    public static function HSL2Hex(float $h, float $s, float $l): string
    {
        $rgb = self::HSL2RGB($h, $s, $l);

        return ColorFunctions::RGB2Hex((int)$rgb['r'], (int)$rgb['g'], (int)$rgb['b']);
    }
}