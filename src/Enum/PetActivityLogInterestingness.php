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

/**
 * This is not actually an enum; just a collection of constant values.
 */
final class PetActivityLogInterestingness
{
    public const int HoHum = 0;
    public const int UncommonActivity = 1000;
    public const int ActivityUsingMerit = 2000;
    public const int ParkEvent = 3000;
    public const int HolidayOrSpecialEvent = 3500;
    public const int LunchboxEmpty = 4000;
    public const int RareActivity = 5000;
    public const int LevelUp = 6000;
    public const int NewRelationship = 6000;
    public const int RelationshipDiscussion = 8000;
    public const int OneTimeQuestActivity = 8500;
    public const int ActivityYieldingPetBadge = 8600;
    public const int GaveBirth = 9000;

    public const int PlayerActionResponse = 9999;
}
