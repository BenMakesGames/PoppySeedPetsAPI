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
