import { Component, OnDestroy, OnInit } from '@angular/core';
import { ApiService } from "../../../shared/service/api.service";
import { Subscription } from "rxjs";
import { TraderCostOrYieldModel } from "../../../../model/trader-cost-or-yield.model";
import { BADGE_INFO } from "../../models/badge-info";

@Component({
    templateUrl: './badges.component.html',
    styleUrls: ['./badges.component.scss'],
    standalone: false
})
export class BadgesComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Achievements - Unclaimed' };

  getAvailableSubscription = Subscription.EMPTY;
  claimingReward = Subscription.EMPTY;

  available: BadgeDto[]|null = null;

  constructor(private api: ApiService)
  {
  }

  doClaim(badge: string)
  {
    if(!this.claimingReward.closed)
      return;

    this.claimingReward = this.api.post('/achievement/claim', { achievement: badge }).subscribe({
      next: () => {
        this.available = this.available
          .filter(b => b.badge !== badge)
          .map(badge => {
            if(badge.badge.startsWith('Achievements'))
              return {
                ...badge,
                progress: { ...badge.progress, current: badge.progress.current + 1 },
                done: badge.progress.current + 1 >= badge.progress.target
              };
            else
              return badge;
          })
          .sort(this.badgeSort)
        ;
      }
    });
  }

  ngOnInit() {
    this.getAvailableSubscription = this.api.get<BadgeDto[]>('/achievement/available').subscribe({
      next: r => {
        this.available = r.data.sort(this.badgeSort);
      }
    })
  }

  badgeSort = (a: BadgeDto, b: BadgeDto) => {
    return Math.min(b.progress.current, b.progress.target) / b.progress.target == Math.min(a.progress.current, a.progress.target) / a.progress.target
      ? BADGE_INFO[a.badge].order - BADGE_INFO[b.badge].order
      : (b.progress.current / b.progress.target) - (a.progress.current / a.progress.target);
  };

  ngOnDestroy() {
    this.getAvailableSubscription.unsubscribe();
  }
}

interface BadgeDto
{
  badge: string;
  progress: { target: number, current: number };
  done: boolean;
  reward: TraderCostOrYieldModel;
}