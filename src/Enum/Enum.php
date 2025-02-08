<?php
declare(strict_types=1);

namespace App\Enum;

use App\Service\IRandom;

trait Enum
{
    /**
     * @return string[]|int[]
     */
    public static function getValues(): array
    {
        return array_values((new \ReflectionClass(__CLASS__))->getConstants());
    }

    public static function isAValue(string|int $value): bool
    {
        return in_array($value, self::getValues());
    }

    public static function getRandomValue(IRandom $rng): string
    {
        return $rng->rngNextFromArray(self::getValues());
    }
}
