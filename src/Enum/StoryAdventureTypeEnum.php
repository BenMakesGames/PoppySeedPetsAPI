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

enum StoryAdventureTypeEnum: string
{
    case Story = 'Story';
    case MineGold = 'MineGold';
    case CollectStone = 'CollectStone';
    case Hunt = 'HuntAutoScaling';
    case Gather = 'Gather';
    case TreasureHunt = 'TreasureHunt';
    case WanderingMonster = 'WanderingMonster';
    case RandomRecruit = 'RecruitTown';

    case RemixShipwreck = 'RemixShipwreck';
    case RemixBeach = 'RemixBeach';
    case RemixForest = 'RemixForest';
    case RemixCave = 'RemixCave';
    case RemixUndergroundLake = 'RemixUndergroundLake';
    case RemixMagicTower = 'RemixMagicTower';
    case RemixUmbralPlants = 'RemixUmbralPlants';
    case RemixDarkVillage = 'RemixDarkVillage';
    case RemixGraveyard = 'RemixGraveyard';
    case RemixTheDeep = 'RemixTheDeep';
    case RemixTreasureRoom = 'RemixTreasureRoom';
}