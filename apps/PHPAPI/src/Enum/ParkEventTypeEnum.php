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

final class ParkEventTypeEnum
{
    use Enum;

    public const string KIN_BALL = 'Kin-Ball';
    public const string TRI_D_CHESS = 'Tri-D Chess';
    public const string JOUSTING = 'Jousting';

    /*
    public const string CTF = 'Capture the Flag';
    public const string SEPAK_TAKRAW = 'Sepak Takraw';
    public const string BADMINTON = 'Badminton';

    public const string TRIDIMENSIONAL_SCRABBLE = 'Tridimensional Scrabble';
    public const string HANDBALL = 'Handball';
    public const string POLE_VAULTING = 'Pole Vaulting';
    */
}
