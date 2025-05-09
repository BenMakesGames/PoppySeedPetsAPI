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

final class PetActivityLogInterestingnessEnum
{
    use FakeEnum;

    public const int HO_HUM = 0;
    public const int UNCOMMON_ACTIVITY = 1000;
    public const int ACTIVITY_USING_MERIT = 2000;
    public const int PARK_EVENT = 3000;
    public const int HOLIDAY_OR_SPECIAL_EVENT = 3500;
    public const int LUNCHBOX_EMPTY = 4000;
    public const int RARE_ACTIVITY = 5000;
    public const int LEVEL_UP = 6000;
    public const int NEW_RELATIONSHIP = 6000;
    public const int RELATIONSHIP_DISCUSSION = 8000;
    public const int ONE_TIME_QUEST_ACTIVITY = 8500;
    public const int ACTIVITY_YIELDING_PET_BADGE = 8600;
    public const int GAVE_BIRTH = 9000;

    public const int PLAYER_ACTION_RESPONSE = 9999;
}
