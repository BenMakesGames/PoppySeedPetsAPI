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

final class PetActivityStatEnum
{
    use Enum;

    public const CRAFT = 'craft';
    public const MAGIC_BIND = 'magicbind';
    public const SMITH = 'smith';
    public const PLASTIC_PRINT = 'plasticprint';
    public const FISH = 'fish';
    public const GATHER = 'gather';
    public const HUNT = 'hunt';
    public const PROTOCOL_7 = 'protocol7';
    public const PROGRAM = 'program';
    public const UMBRA = 'umbra'; // do NOT rename this to "Arcana" - this actually represents time spent in the Umbra!
    public const PARK_EVENT = 'parkevent';
    public const OTHER = 'other';
}
