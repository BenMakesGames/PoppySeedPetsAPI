<?php
namespace App\Enum;

final class HollowEarthRequiredActionEnum
{
    use Enum;

    public const NO = 0;
    public const YES_AND_KEEP_MOVING = 1;
    public const YES_AND_STOP_MOVING = 2;
}