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

use App\Service\IRandom;
use App\Service\Squirrel3;

final class StringFunctions
{
    public static function isTruthy($v): bool
    {
        if(is_bool($v)) return $v;

        if(is_numeric($v)) return $v != 0;

        if(is_string($v)) return mb_strtolower(trim($v)) === 'true';

        return false;
    }

    public static function escapeMySqlWildcardCharacters(string $string): string
    {
        return addcslashes($string, '%_');
    }

    public static function isISO88591(string $string): bool
    {
        $ISOd = iconv('UTF-8', 'ISO-8859-1//IGNORE', $string);
        return mb_strlen($string) === strlen($ISOd);
    }
}
