import { Component, OnDestroy, OnInit } from '@angular/core';
import {MyAccountSerializationGroup} from "../../../../model/my-account/my-account.serialization-group";
import {ApiService} from "../../../shared/service/api.service";
import {RecyclingResultModel} from "../../../../model/recycling-result.model";
import {ApiResponseModel} from "../../../../model/api-response.model";
import { Subscription } from "rxjs";
import { UserDataService } from "../../../../service/user-data.service";
import { HasSounds, SoundsService } from "../../../shared/service/sounds.service";

@Component({
    selector: 'app-recycling-game',
    templateUrl: './recycling-game.component.html',
    styleUrls: ['./recycling-game.component.scss'],
    standalone: false
})
@HasSounds([ 'roll-die-a', 'roll-die-b', 'roll-die-c' ])
export class RecyclingGameComponent implements OnInit, OnDestroy {

  state = 'welcome';
  rolling = false;
  results: RecyclingResultModel;
  resultsDialog: string;

  user: MyAccountSerializationGroup|null = null;
  userSubscription = Subscription.EMPTY;

  constructor(
    private api: ApiService, private userData: UserDataService, private sounds: SoundsService
  ) {
  }

  ngOnInit() {
    this.userSubscription = this.userData.user.subscribe(u => this.user = u);
  }

  ngOnDestroy() {
    this.userSubscription.unsubscribe();
  }

  doRoll(bet: number)
  {
    if(this.rolling) return;

    this.rolling = true;

    this.api.post<RecyclingResultModel>('/florist/rollSatyrDice', { bet: bet }).subscribe({
      next: (r: ApiResponseModel<RecyclingResultModel>) => {
        this.sounds.playRandomSound([ 'roll-die-a', 'roll-die-b', 'roll-die-c' ]);
        this.rolling = false;
        this.results = r.data;
        this.resultsDialog = this.generateResultsDialog(this.results);
      },
      error: () => {
        this.rolling = false;
      }
    })
  }

  private generateResultsDialog(r: RecyclingResultModel): string
  {
    let parts = [];

    let total = r.dice[0] + r.dice[1];

    if(total >= 11 || total <= 3)
      parts.push('Oh, dang! ' + total + '!');
    else
      parts.push('That\'s a ' + total + '!');

    if(r.getDouble)
    {
      if(total === 8)
        parts.push('And you nailed it!');
      else if(total < 5)
        parts.push('But oh, nice! You called it!');
      else
        parts.push('And oh, nice! You called it!');

      parts.push('That\'s');
    }
    else
    {
      if(total < 8)
        parts.push('Less than an 8... ');
      else if(total > 8)
        parts.push('More than an 8... ');

      parts.push('but hey, you still get');
    }

    let rewards = r.items;

    if(r.points)
      rewards.push(r.points + '♺');

    let rewardString = rewards.listNice();

    if(total < 8 && !r.getDouble)
      rewardString += '.';
    else
      rewardString += '!';

    parts.push(rewardString);

    return parts.join(' ');
  }
}
