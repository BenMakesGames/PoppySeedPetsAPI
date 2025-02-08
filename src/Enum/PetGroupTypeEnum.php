<?php
declare(strict_types=1);

namespace App\Enum;

class PetGroupTypeEnum
{
    use Enum;

    public const BAND = 1;
    public const ASTRONOMY = 2;
    public const GAMING = 3;
    public const SPORTSBALL = 4;
}