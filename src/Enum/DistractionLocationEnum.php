<?php
declare(strict_types=1);

namespace App\Enum;

class DistractionLocationEnum
{
    use Enum;

    public const WOODS = 'woods';
    public const UNDERGROUND = 'underground';
    public const BEACH = 'beach';
    public const VOLCANO = 'volcano';
    public const IN_TOWN = 'inTown';
}