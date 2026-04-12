/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, Inject, OnDestroy, OnInit} from '@angular/core';
import {ApiService} from "../../service/api.service";
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import {FilterResultsSerializationGroup} from "../../../../model/filter-results.serialization-group";
import {PetActivitySerializationGroup} from "../../../../model/pet-activity-logs/pet-activity.serialization-group";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {UserPublicProfilePetSerializationGroup} from "../../../../model/public-profile/user-public-profile-pet.serialization-group";
import {Subscription} from "rxjs";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";
import { CommonModule, DatePipe } from "@angular/common";
import { LoadingThrobberComponent } from "../../component/loading-throbber/loading-throbber.component";
import { PetActivityLogTableComponent } from "../../component/pet-activity-log-table/pet-activity-log-table.component";

@Component({
    templateUrl: './pet-daily-logs.dialog.html',
    imports: [
        DatePipe,
        LoadingThrobberComponent,
        PetActivityLogTableComponent,
        CommonModule
    ],
    styleUrls: ['./pet-daily-logs.dialog.scss']
})
export class PetDailyLogsDialog implements OnInit, OnDestroy {

  pet: MyPetSerializationGroup;
  date: Date;
  logs: FilterResultsSerializationGroup<PetActivitySerializationGroup>;
  petLogsAjax: Subscription;

  constructor(
    private dialog: MatDialogRef<PetDailyLogsDialog>,
    @Inject(MAT_DIALOG_DATA) private data: any,
    private api: ApiService
  ) {
    this.pet = data.pet;
    this.date = data.date;
  }

  ngOnInit(): void
  {
    const data = {
      filter: {
        date: this.date.toISOString().substr(0, 10)
      }
    };

    this.petLogsAjax = this.api.get<FilterResultsSerializationGroup<PetActivitySerializationGroup>>('/pet/' + this.pet.id + '/logs', data).subscribe({
      next: (r: ApiResponseModel<FilterResultsSerializationGroup<PetActivitySerializationGroup>>) => {
        this.logs = r.data;
      }
    });
  }

  ngOnDestroy(): void {
    this.petLogsAjax.unsubscribe();
  }

  public static open(matDialog: MatDialog, pet: MyPetSerializationGroup|UserPublicProfilePetSerializationGroup, date: Date): MatDialogRef<PetDailyLogsDialog>
  {
    return matDialog.open(PetDailyLogsDialog, {
      data: {
        pet: pet,
        date: date
      }
    });
  }

}
