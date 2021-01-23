<?php
namespace App\Enum;

use App\Functions\ArrayFunctions;
use App\Service\Squirrel3;

trait Enum
{
    public static function getValues(): array
    {
        return array_values((new \ReflectionClass(__CLASS__))->getConstants());
    }

    public static function isAValue(string $value): bool
    {
        return in_array($value, self::getValues());
    }

    public static function getRandomValue(Squirrel3 $squirrel3): string
    {
        return $squirrel3->rngNextFromArray(self::getValues());
    }
}
