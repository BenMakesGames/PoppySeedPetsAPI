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

namespace App\Enum;

use App\Service\IRandom;

/**
 * @template T
 */
#[\Deprecated('This trait is deprecated and should not be used. Use native PHP enums instead.')]
trait FakeEnum
{
    /**
     * @return list<T>
     */
    public static function getValues(): array
    {
        /** @phpstan-ignore-next-line */
        return array_values((new \ReflectionClass(__CLASS__))->getConstants());
    }

    /**
     * @param T $value
     */
    public static function isAValue(mixed $value): bool
    {
        return in_array($value, self::getValues());
    }

    /**
     * @return T
     */
    public static function getRandomValue(IRandom $rng): mixed
    {
        return $rng->rngNextFromArray(self::getValues());
    }
}
