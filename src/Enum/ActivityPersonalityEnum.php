<?php
namespace App\Enum;

class ActivityPersonalityEnum
{
    use Enum;

    // 0-15 are gathering
    public const GATHERING = 1 << 0;
    public const FISHING = 1 << 1;
    public const HUNTING = 1 << 2;
    public const BEANSTALK = 1 << 3;
    public const SUBMARINE = 1 << 4;
    public const UMBRA = 1 << 5;
    public const PROTOCOL_7 = 1 << 6;
    public const ICY_MOON = 1 << 7;

    public const EVENTS_AND_MAPS = 1 << 15;

    // 16-31 are crafting
    public const CRAFTING_MUNDANE = 1 << 16;
    public const CRAFTING_SMITHING = 1 << 17;
    public const CRAFTING_MAGIC = 1 << 18;
    public const CRAFTING_SCIENCE = 1 << 19;
    public const CRAFTING_PLASTIC = 1 << 20;
}