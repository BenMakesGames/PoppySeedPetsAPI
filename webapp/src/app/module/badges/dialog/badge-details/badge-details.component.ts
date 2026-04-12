/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, Inject, OnDestroy, OnInit } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";
import { ApiService } from "../../../shared/service/api.service";
import { Subscription } from "rxjs";
import { FilterResultsSerializationGroup } from "../../../../model/filter-results.serialization-group";
import { PetPublicProfileSerializationGroup } from "../../../../model/public-profile/pet-public-profile.serialization-group";
import { UserDataService } from "../../../../service/user-data.service";
import { LoadingThrobberComponent } from "../../../shared/component/loading-throbber/loading-throbber.component";
import { PetAppearanceComponent } from "../../../shared/component/pet-appearance/pet-appearance.component";
import { RouterLink } from "@angular/router";
import { PetBadgeNamePipe } from "../../../shared/pipe/pet-badge-name.pipe";
import { PaginatorComponent } from "../../../shared/component/paginator/paginator.component";
import { petBadgeInfo } from "../../../shared/pet-badge-info";
import { MarkdownComponent } from "ngx-markdown";

@Component({
    imports: [
        LoadingThrobberComponent,
        PetAppearanceComponent,
        RouterLink,
        PetBadgeNamePipe,
        PaginatorComponent,
        MarkdownComponent
    ],
    templateUrl: './badge-details.component.html',
    styleUrl: './badge-details.component.scss'
})
export class BadgeDetailsComponent implements OnInit, OnDestroy {

  pets: FilterResultsSerializationGroup<PetPublicProfileSerializationGroup>|null = null;
  petsSubscription = Subscription.EMPTY;

  badge: string;
  hint: 'no'|'yes'|'spoil' = 'no';
  hintText: string|null = null;

  constructor(
    private readonly dialogRef: MatDialogRef<BadgeDetailsComponent>,
    private readonly userData: UserDataService,
    @Inject(MAT_DIALOG_DATA) private readonly data: any,
    private readonly api: ApiService,
  ) {
    this.badge = data.badge;
  }

  ngOnInit() {
    this.doChangePage(0);
  }

  ngOnDestroy() {
    this.petsSubscription.unsubscribe();
  }

  doCloseDialog()
  {
    this.dialogRef.close();
  }

  doChangeHint(hint: 'no'|'yes'|'spoil')
  {
    this.hint = hint;

    switch(hint)
    {
      case 'no': this.hintText = null; return;
      case 'yes': this.hintText = '**Hint:** ' + petBadgeInfo[this.badge].hint; return;
      case 'spoil': this.hintText = '**Spoiler:** ' + petBadgeInfo[this.badge].spoiler; return;
    }
  }

  doChangePage(page = 0)
  {
    this.petsSubscription.unsubscribe();

    const data = {
      page: page,
      filter: {
        owner: this.userData.user.value.id,
        badge: this.badge,
      }
    };

    this.petsSubscription = this.api.get<FilterResultsSerializationGroup<PetPublicProfileSerializationGroup>>('/pet', data).subscribe({
      next: r => {
        this.pets = r.data;
      }
    });
  }

  public static open(matDialog: MatDialog, badge: string) {
    matDialog.open(BadgeDetailsComponent, {
      data: { badge },
      width: '100%',
      maxWidth: 'min(80vw, 800px)',
    });
  }
}
