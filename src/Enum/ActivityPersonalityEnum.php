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