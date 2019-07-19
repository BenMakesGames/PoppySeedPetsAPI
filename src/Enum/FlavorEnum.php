<?php
namespace App\Enum;

use App\Functions\ArrayFunctions;

final class FlavorEnum
{
    const EARTHY = 'earthy';
    const FRUITY = 'fruity';
    const TANNIC = 'tannic';
    const SPICY = 'spicy';
    const CREAMY = 'creamy';
    const MEATY = 'meaty';
    const PLANTY = 'planty';
    const FISHY = 'fishy';
    const FLORAL = 'floral';
    const FATTY = 'fatty';
    const ONIONY = 'oniony';
    const CHEMICALY = 'chemicaly';

    public static function isAFlavor(string $flavor): bool
    {
        $flavors = (new \ReflectionClass(__CLASS__))->getConstants();

        return in_array($flavor, $flavors);
    }

    public static function getRandom(): string
    {
        $flavors = (new \ReflectionClass(__CLASS__))->getConstants();

        return ArrayFunctions::pick_one($flavors);
    }
}