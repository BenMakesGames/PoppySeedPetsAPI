<?php
declare(strict_types=1);

namespace App\Functions;

final class CacheHelpers
{
    public static function getCacheItemName(string $name): string
    {
        return str_replace([ '\'', '{', '}', '(', ')', '/', '\\', '@', ':' ], '--', $name);
    }
}