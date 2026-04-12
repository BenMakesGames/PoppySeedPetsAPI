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
import { PetBadgeNamePipe } from "../../../shared/pipe/pet-badge-name.pipe";
import { BadgeDetailsComponent } from "../../dialog/badge-details/badge-details.component";
import { MatDialog } from "@angular/material/dialog";

@Component({
    templateUrl: './pet-badges.component.html',
    styleUrls: ['./pet-badges.component.scss'],
    standalone: false
})
export class PetBadgesComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Achievements - Pet Badges' };

  petBadgesSubscription = Subscription.EMPTY;
  petBadges: PetBadgeRow[]|null = null;

  sort = 'recentlyAchieved';

  constructor(
    private readonly api: ApiService,
    private readonly matDialog: MatDialog
  )
  {
  }

  ngOnInit() {
    this.petBadgesSubscription = this.api.get<PetBadgeRow[]>('/achievement/petBadges').subscribe({
      next: r => {
        this.petBadges = r.data;
        this.doSort();
      }
    });
  }

  doSort()
  {
    if(this.sort === 'recentlyAchieved')
    {
      this.petBadges.sort((a, b) => {
        if(a.firstAchievedOn == null && b.firstAchievedOn == null) return PetBadgesComponent.badgeNameSort(a, b);
        if(a.firstAchievedOn == null) return 1;
        if(b.firstAchievedOn == null) return -1;
        return b.firstAchievedOn.localeCompare(a.firstAchievedOn);
      });
    }
    else if(this.sort === 'name')
    {
      this.petBadges.sort(PetBadgesComponent.badgeNameSort);
    }
    else if(this.sort === 'mostAchieved')
    {
      this.petBadges.sort((a, b) => {
        if(a.pets === b.pets) return PetBadgesComponent.badgeNameSort(a, b);
        return b.pets - a.pets;
      });
    }
  }

  doViewBadge(badge: string)
  {
    BadgeDetailsComponent.open(this.matDialog, badge);
  }

  private static badgeNameSort(a: PetBadgeRow, b: PetBadgeRow)
  {
    return PetBadgeNamePipe.getBadgeName(a.badge).localeCompare(PetBadgeNamePipe.getBadgeName(b.badge));
  }

  ngOnDestroy() {
    this.petBadgesSubscription.unsubscribe();
  }
}

interface PetBadgeRow
{
  badge: string;
  firstAchievedOn: string;
  pets: number;
}