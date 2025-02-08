<?php
declare(strict_types=1);

namespace App\Enum;

final class HollowEarthMoveDirectionEnum
{
    use Enum;

    public const NORTH = 'N';
    public const EAST = 'E';
    public const SOUTH = 'S';
    public const WEST = 'W';
    public const ZERO = 'Z';
}