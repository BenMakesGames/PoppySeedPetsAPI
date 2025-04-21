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

final class UnlockableFeatureEnum
{
    use Enum;

    public const string Florist = 'Florist';
    public const string Bookstore = 'Bookstore';
    public const string Museum = 'Museum';
    public const string Park = 'Park';
    public const string Greenhouse = 'Greenhouse';
    public const string CookingBuddy = 'Cooking Buddy';
    public const string Basement = 'Basement';
    public const string HollowEarth = 'Hollow Earth';
    public const string Market = 'Market';
    public const string Fireplace = 'Fireplace';
    public const string Beehive = 'Beehive';
    public const string Trader = 'Trader';
    public const string Mailbox = 'Mailbox';
    public const string DragonDen = 'Dragon Den';
    public const string BulkSelling = 'Bulk Selling';
    public const string Hattier = 'Hattier';
    public const string FieldGuide = 'Field Guide';
    public const string StarKindred = 'Star Kindred';
    public const string Zoologist = 'Zoologist';
}