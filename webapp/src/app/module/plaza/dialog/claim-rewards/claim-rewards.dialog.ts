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