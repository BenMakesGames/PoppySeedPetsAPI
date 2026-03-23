import {Component, Inject, OnDestroy} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {UserDataService} from "../../../../service/user-data.service";
import {MyAccountSerializationGroup} from "../../../../model/my-account/my-account.serialization-group";
import {FilterResultsSerializationGroup} from "../../../../model/filter-results.serialization-group";
import {PetActivitySerializationGroup} from "../../../../model/pet-activity-logs/pet-activity.serialization-group";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {ChoiceModel} from "../../../../dialog/choose-one/choose-one.dialog";
import {Subscription} from "rxjs";
import { MyPetSerializationGroup } from "../../../../model/my-pet/my-pet.serialization-group";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";
import { LoadingThrobberComponent } from "../../../shared/component/loading-throbber/loading-throbber.component";
import { PetLogsLinksComponent } from "../../../shared/component/pet-logs-links/pet-logs-links.component";
import { PetActivityLogTableComponent } from "../../../shared/component/pet-activity-log-table/pet-activity-log-table.component";
import { PetFriendsComponent } from "../../../shared/component/pet-friends/pet-friends.component";
import { PetSkillsAndAttributesPanelComponent } from "../../../shared/component/pet-skills-and-attributes-panel/pet-skills-and-attributes-panel.component";
import { PetMeritsComponent } from "../../../shared/component/pet-merits/pet-merits.component";
import { PetBadgeTableComponent } from "../../../shared/pet-badge-table/pet-badge-table.component";
import { PetNotesComponent } from "../../../shared/component/pet-notes/pet-notes.component";
import { PetStatusEffectsTabComponent } from "../../../pet-management/components/pet-status-effects-tab/pet-status-effects-tab.component";

@Component({
  selector: 'app-interact-with-away-pet',
  templateUrl: './interact-with-away-pet-dialog.component.html',
  imports: [
    LoadingThrobberComponent,
    PetLogsLinksComponent,
    PetActivityLogTableComponent,
    PetFriendsComponent,
    PetSkillsAndAttributesPanelComponent,
    PetMeritsComponent,
    PetBadgeTableComponent,
    PetNotesComponent,
    PetStatusEffectsTabComponent
  ],
  styleUrls: [ './interact-with-away-pet-dialog.component.scss' ]
})
export class InteractWithAwayPetDialog implements OnDestroy {

  loading = true;
  viewedFriends = false;
  everViewedLogs = false;
  tab: string = 'status';
  petNote = '';

  petName: string;
  pet: MyPetSerializationGroup|null = null;
  user: MyAccountSerializationGroup;

  logs: FilterResultsSerializationGroup<PetActivitySerializationGroup>;

  choices: ChoiceModel[];

  petSubscription = Subscription.EMPTY;
  petLogsAjax = Subscription.EMPTY;

  constructor(
    private dialogRef: MatDialogRef<InteractWithAwayPetDialog>,
    @Inject(MAT_DIALOG_DATA) private data: any,
    private api: ApiService,
    private userData: UserDataService,
  )
  {
    this.petName = data.petName;
    this.user = this.userData.user.getValue();
    this.choices = data.choices;

    this.petSubscription = this.api.get<MyPetSerializationGroup>(`/pet/my/${this.data.petId}`).subscribe(r => {
      this.pet = r.data;
      this.petNote = this.pet.note;
      this.loading = false;
    });
  }

  ngOnDestroy(): void {
    this.petSubscription.unsubscribe();
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

  doRename(newPet: MyPetSerializationGroup)
  {
    this.petName = newPet.name;

    this.dialogRef.close({ newPet: newPet });
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

  public static open(matDialog: MatDialog, petId: number, petName: string, choices: ChoiceModel[]): MatDialogRef<InteractWithAwayPetDialog>
  {
    return matDialog.open(InteractWithAwayPetDialog, {
      data: {
        petId: petId,
        petName: petName,
        choices: choices
      }
    });
  }
}
