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

final class TradeGroupEnum
{
    use Enum;

    public const int METALS = 1;
    public const int DARK_THINGS = 2;
    //public const int FOODS = 3;
    public const int HOLLOW_EARTH = 4;
    //public const int BOX_BOX = 5;
    public const int CURIOSITIES = 6;
    public const int PLUSHIES = 7;
    public const int BLEACH = 8;
    public const int DIGITAL = 9;
    public const int BUGS = 10;
}
