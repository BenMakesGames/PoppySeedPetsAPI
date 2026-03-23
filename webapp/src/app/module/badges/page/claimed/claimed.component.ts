import { Component, OnDestroy, OnInit } from '@angular/core';
import { ApiService } from "../../../shared/service/api.service";
import { Subscription } from "rxjs";
import { BADGE_GROUP_ICONS, BADGE_INFO } from "../../models/badge-info";

@Component({
    templateUrl: './claimed.component.html',
    styleUrls: ['./claimed.component.scss'],
    standalone: false
})
export class ClaimedComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Achievements - Claimed' };

  getClaimedSubscription = Subscription.EMPTY;

  BADGE_GROUP_ICONS = BADGE_GROUP_ICONS;
  claimed: { group: string, badges: { badge: string, claimedOn: string }[]}[]|null = null;
  numberUnlockedInGroup: { [key: string]: number } = {};
  groupSizes: { [key: string]: number } = {};

  constructor(private api: ApiService)
  {
  }

  ngOnInit() {
    const badges = Object.keys(BADGE_INFO)
    for(let i = 0; i < badges.length; i++)
    {
      this.groupSizes[BADGE_INFO[badges[i]].group] = (this.groupSizes[BADGE_INFO[badges[i]].group] ?? 0) + 1;
    }

    this.getClaimedSubscription = this.api.get<{ badge: string, claimedOn: string }[]>('/achievement').subscribe({
      next: r => {
        this.claimed = [];

        const badges = r.data.sort((a, b) => BADGE_INFO[a.badge].order - BADGE_INFO[b.badge].order);

        for(let i = 0; i < badges.length; i++)
        {
          const badge = badges[i];
          const info = BADGE_INFO[badge.badge];

          if(this.claimed.length == 0 || this.claimed[this.claimed.length - 1].group != info.group)
            this.claimed.push({ group: info.group, badges: [] });

          this.claimed[this.claimed.length - 1].badges.push(badge);

          this.numberUnlockedInGroup[info.group] = (this.numberUnlockedInGroup[info.group] ?? 0) + 1;
        }
      }
    });
  }

  ngOnDestroy() {
    this.getClaimedSubscription.unsubscribe();
  }
}
