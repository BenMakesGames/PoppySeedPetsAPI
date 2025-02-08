<?php
declare(strict_types=1);

namespace App\Functions;

final class ULID
{
    public static function generateUUID(?int $timeInMs = null): string
    {
        $uuidHex = self::generateHex($timeInMs);

        // format as UUID
        return sprintf('%s-%s-%s-%s-%s',
            substr($uuidHex, 0, 8),
            substr($uuidHex, 8, 4),
            substr($uuidHex, 12, 4),
            substr($uuidHex, 16, 4),
            substr($uuidHex, 20, 12)
        );
    }

    public static function generateHex(?int $timeInMs = null): string
    {
        $timeInMs ??= (int)(microtime(true) * 1000);

        // store time in 6 bytes (48 bits)
        $timeInMs = $timeInMs & ((1 << 48) - 1);

        // generate 10 random bytes (80 bits)
        $randomBytes = random_bytes(10);

        // convert to a hex string
        return sprintf('%012x', $timeInMs) . bin2hex($randomBytes);
    }

    public static function generateBinary(?int $timeInMs = null): string
    {
        $timeInMs ??= (int)(microtime(true) * 1000);

        // get 48 bits of time:
        $timeInMs = $timeInMs & ((1 << 48) - 1);

        // convert timeInMs to a string of 6 bytes
        $timeBytes = substr(pack('J', $timeInMs), 2);

        // concatenate 10 random bytes (80 bits)
        return $timeBytes . random_bytes(10);
    }
}