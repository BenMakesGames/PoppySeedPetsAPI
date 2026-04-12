/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, OnDestroy } from '@angular/core';
import { MatDialog, MatDialogRef } from "@angular/material/dialog";
import { ApiResponseModel } from "../../../../model/api-response.model";
import { ApiService } from "../../../shared/service/api.service";
import { Subscription } from "rxjs";
import { MessagesService } from "../../../../service/messages.service";

@Component({
    templateUrl: './claim-rewards.dialog.html',
    styleUrls: ['./claim-rewards.dialog.scss'],
    standalone: false
})
export class ClaimRewardsDialog implements OnDestroy {

  getHistory: Subscription;
  claiming = Subscription.EMPTY;
  rewards: MonsterRewards[]|null = null;

  today: string;
  todayInterval: number;
  voracious: string;

  constructor(
    private dialogRef: MatDialogRef<ClaimRewardsDialog>,
    private api: ApiService, private messages: MessagesService
  ) {
    this.voracious = [
      'ravenous',
      'voracious',
      'insatiable'
    ][Math.floor(Math.random() * 3)];

    this.getHistory = this.api.get<MonsterRewards[]>('/monsterOfTheWeek/rewards').subscribe({
      next: (r: ApiResponseModel<MonsterRewards[]>) => {
        this.rewards = r.data;
      }
    });

    this.today = ClaimRewardsDialog.getFormattedToday();

    this.todayInterval = window.setInterval(() => {
      this.today = ClaimRewardsDialog.getFormattedToday();
    }, 5000);
  }

  private static getFormattedToday(): string
  {
    return (new Date()).toISOString().substring(0, 10);
  }

  doClaimReward(monsterId: number)
  {
    if(!this.claiming.closed) return;

    this.dialogRef.disableClose = true;

    this.claiming = this.api.post<string>('/monsterOfTheWeek/' + monsterId + '/claimRewards').subscribe({
      next: r => {
        this.messages.addGenericMessage(r.data);

        this.rewards = this.rewards.map(r => {
          if(r.id == monsterId)
            return { ...r, rewardsClaimed: true };

          return r;
        });

        this.dialogRef.disableClose = false;
      },
      error: () => {
        this.dialogRef.disableClose = false;
      }
    });
  }

  ngOnDestroy() {
    clearInterval(this.todayInterval);
    this.getHistory.unsubscribe();
  }

  doClose()
  {
    this.dialogRef.close();
  }

  public static open(matDialog: MatDialog): MatDialogRef<ClaimRewardsDialog>
  {
    return matDialog.open(ClaimRewardsDialog);
  }
}

interface MonsterRewards
{
  id: number;
  startDate: string;
  endDate: string;
  type: string;
  personalContribution: number;
  communityTotal: number;
  rewardsClaimed: boolean;
  milestones: { value: number, item: { name: string, image: string } }[];
}