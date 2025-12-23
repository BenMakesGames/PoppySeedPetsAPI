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

final class PlayerActivityLogTagEnum
{
    use FakeEnum;

    public const string Moneys = 'Moneys';
    public const string Recycling = 'Recycling';
    public const string Market = 'Market';
    public const string Fireplace = 'Fireplace';
    public const string Greenhouse = 'Greenhouse';
    public const string Beehive = 'Beehive';
    public const string Dragon_Den = 'Dragon Den';
    public const string Account_and_Security = 'Account & Security';
    public const string Museum = 'Museum';
    public const string Grocer = 'Grocer';
    public const string Hattier = 'Hattier';
    public const string Satyr_Dice = 'Satyr Dice';
    public const string Earth_Day = 'Earth Day';
    public const string Fae_kind = 'Fae-kind';
    public const string Trader = 'Trader';
    public const string Shirikodama = 'Shirikodama';
    public const string Halloween = 'Halloween';
    public const string Special_Event = 'Special Event';
    public const string Stocking_Stuffing_Season = 'Stocking Stuffing Season';
    public const string Birdbath = 'Birdbath';
    public const string Item_Use = 'Item Use';
}
