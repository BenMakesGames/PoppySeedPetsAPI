/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
