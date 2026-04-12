/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
    name: 'missionTitle',
    standalone: false
})
export class MissionTitlePipe implements PipeTransform {

  transform(value: string): string {
    if(value.startsWith('Remix'))
      return 'REMIX';

    switch(value)
    {
      case 'RecruitTown': return 'Recruiting';
      case 'Oracle': return 'Oracle';
      case 'HuntAutoScaling': return 'Hunting Game';
      case 'HuntLevel0': return 'Hunting Game (Level 0)';
      case 'HuntLevel10': return 'Hunting Game (Level 10)';
      case 'HuntLevel20': return 'Hunting Game (Level 20)';
      case 'HuntLevel50': return 'Hunting Game (Level 50)';
      case 'HuntLevel80': return 'Hunting Game (Level 80)';
      case 'HuntLevel120': return 'Hunting Game (Level 120)';
      case 'HuntLevel200': return 'Hunting Game (Level 200)';

      case 'WanderingMonster': return 'Wandering Monster';
      case 'Settlers': return 'Settler Caravan';
      case 'TreasureHunt': return 'Treasure Hunt';

      case 'Gather': return 'Gathering';
      case 'Story': return 'Narrative';
      case 'CollectStone': return 'Collect Stone';
      case 'MineGold': return 'Collect Gold';

      case 'BoatDate': return 'Boat Tour';

      default: return '???';
    }
  }

}
