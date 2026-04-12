/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
