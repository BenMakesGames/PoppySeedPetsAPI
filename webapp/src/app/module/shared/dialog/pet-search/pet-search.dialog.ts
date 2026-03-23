import { Component, Inject } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";
import { CommonModule } from "@angular/common";
import { FormsModule } from "@angular/forms";
import { UserDataService } from "../../../../service/user-data.service";
import { MyAccountSerializationGroup } from "../../../../model/my-account/my-account.serialization-group";
import { FindPetSpeciesByNameComponent } from "../../component/find-pet-species-by-name/find-pet-species-by-name.component";
import { PetSearchModel } from "../../../../model/search/pet-search-model";
import { SelectMeritComponent } from "../../component/select-merit/select-merit.component";
import { FindItemByNameComponent } from "../../component/find-item-by-name/find-item-by-name.component";
import { InputYesNoBothComponent } from "../../../filters/components/input-yes-no-both/input-yes-no-both.component";

@Component({
  templateUrl: './pet-search.dialog.html',
  imports: [
    CommonModule,
    FormsModule,
    FindPetSpeciesByNameComponent,
    SelectMeritComponent,
    FindItemByNameComponent,
    InputYesNoBothComponent
  ],
  styleUrls: ['./pet-search.dialog.scss']
})
export class PetSearchDialog
{
  isOctober = false;

  filter: PetSearchModel;
  user: MyAccountSerializationGroup;

  constructor(
    @Inject(MAT_DIALOG_DATA) data,
    private dialogRef: MatDialogRef<PetSearchDialog>,
    private userData: UserDataService,
  )
  {
    this.filter = data.filter;
    this.user = userData.user.getValue();
  }

  doSearch()
  {
    this.dialogRef.close(this.filter);
  }

  doClose()
  {
    this.dialogRef.close();
  }

  public static open(matDialog: MatDialog, filter: PetSearchModel): MatDialogRef<PetSearchDialog>
  {
    return matDialog.open(PetSearchDialog, {
      width: '6.5in',
      data: {
        filter: { ... filter }
      }
    });
  }
}
