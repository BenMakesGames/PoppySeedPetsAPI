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

class PlayerActivityLogTagEnum
{
    use Enum;

    public const Moneys = 'Moneys';
    public const Recycling = 'Recycling';
    public const Market = 'Market';
    public const Fireplace = 'Fireplace';
    public const Greenhouse = 'Greenhouse';
    public const Beehive = 'Beehive';
    public const Dragon_Den = 'Dragon Den';
    public const Account_and_Security = 'Account & Security';
    public const Museum = 'Museum';
    public const Grocer = 'Grocer';
    public const Hattier = 'Hattier';
    public const Satyr_Dice = 'Satyr Dice';
    public const Earth_Day = 'Earth Day';
    public const Fae_kind = 'Fae-kind';
    public const Trader = 'Trader';
    public const Shirikodama = 'Shirikodama';
    public const Halloween = 'Halloween';
    public const Special_Event = 'Special Event';
    public const Stocking_Stuffing_Season = 'Stocking Stuffing Season';
    public const Birdbath = 'Birdbath';
    public const Item_Use = 'Item Use';
}
