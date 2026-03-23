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
