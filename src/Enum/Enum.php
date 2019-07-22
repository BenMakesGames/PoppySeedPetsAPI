<?php
namespace App\Enum;

use App\Functions\ArrayFunctions;

trait Enum
{
    public static function getValues(): array
    {
        return (new \ReflectionClass(__CLASS__))->getConstants();
    }

    public static function isAValue(string $value): bool
    {
        return in_array($value, self::getValues());
    }

    public static function getRandomValue(): string
    {
        return ArrayFunctions::pick_one(self::getValues());
    }
}