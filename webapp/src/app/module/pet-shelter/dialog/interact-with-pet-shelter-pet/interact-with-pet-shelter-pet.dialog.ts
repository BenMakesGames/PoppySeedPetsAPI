import {Component, Inject, OnDestroy} from '@angular/core';
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import {ApiService} from "../../../shared/service/api.service";
import {UserDataService} from "../../../../service/user-data.service";
import {MyAccountSerializationGroup} from "../../../../model/my-account/my-account.serialization-group";
import {FilterResultsSerializationGroup} from "../../../../model/filter-results.serialization-group";
import {PetActivitySerializationGroup} from "../../../../model/pet-activity-logs/pet-activity.serialization-group";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {ChoiceModel} from "../../../../dialog/choose-one/choose-one.dialog";
import {Subscription} from "rxjs";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";

@Component({
    selector: 'app-interact-with-pet-shelter-pet',
    templateUrl: './interact-with-pet-shelter-pet.dialog.html',
    styleUrls: ['./interact-with-pet-shelter-pet.dialog.scss'],
    standalone: false
})
export class InteractWithPetShelterPetDialog implements OnDestroy {

  loading = false;
  viewedFriends = false;
  everViewedLogs = false;
  tab: string = 'status';
  petNote = '';
  hasVolagamy = false;

  pet: MyPetSerializationGroup;
  user: MyAccountSerializationGroup;

  logs: FilterResultsSerializationGroup<PetActivitySerializationGroup>;

  choices: ChoiceModel[];

  petLogsAjax = Subscription.EMPTY;

  constructor(
    private dialogRef: MatDialogRef<InteractWithPetShelterPetDialog>,
    @Inject(MAT_DIALOG_DATA) private data: any,
    private api: ApiService,
    private userData: UserDataService,
  )
  {
    this.pet = data.pet;
    this.user = this.userData.user.getValue();
    this.choices = data.choices;
    this.petNote = this.pet.note;
    this.hasVolagamy = this.pet.merits.some(m => m.name === 'Volagamy');
  }

  ngOnDestroy(): void {
    this.petLogsAjax.unsubscribe();
  }

  doLoadLogs()
  {
    if(this.everViewedLogs)
      return;

    this.everViewedLogs = true;

    this.petLogsAjax = this.api.get<FilterResultsSerializationGroup<PetActivitySerializationGroup>>('/pet/' + this.pet.id + '/logs').subscribe({
      next: (r: ApiResponseModel<FilterResultsSerializationGroup<PetActivitySerializationGroup>>) => {
        this.logs = r.data;
      }
    });
  }

  doChangeTab(tab: string)
  {
    if(this.loading) return;

    this.tab = tab;

    if(this.tab === 'friends')
      this.viewedFriends = true;
    else if(this.tab === 'logs')
      this.doLoadLogs();
  }

  doMakeChoice(choice: ChoiceModel)
  {
    this.dialogRef.close(choice);
  }

  doClose()
  {
    this.dialogRef.close();
  }

  doLoading(loading: boolean)
  {
    this.loading = loading;
  }

  public static open(matDialog: MatDialog, pet: MyPetSerializationGroup, choices: ChoiceModel[]): MatDialogRef<InteractWithPetShelterPetDialog>
  {
    return matDialog.open(InteractWithPetShelterPetDialog, {
      data: {
        pet: pet,
        choices: choices
      }
    })
  }
}
