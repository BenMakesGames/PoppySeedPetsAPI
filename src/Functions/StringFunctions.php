<?php
declare(strict_types=1);

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
