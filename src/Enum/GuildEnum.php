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

enum GuildEnum: string
{
    case TimesArrow = 'Time\'s Arrow';
    case LightAndShadow = 'Light and Shadow';
    case Tapestries = 'Tapestries';
    case InnerSanctum = 'Inner Sanctum';
    case Dwarfcraft = 'Dwarfcraft';
    case GizubisGarden = 'Gizubi\'s Garden';
    case HighImpact = 'High Impact';
    case TheUniverseForgets = 'The Universe Forgets';
    case Correspondence = 'Correspondence';
}
