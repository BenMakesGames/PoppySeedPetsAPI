/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, Inject, Input } from '@angular/core';
import { MyPetSerializationGroup } from "../../../../model/my-pet/my-pet.serialization-group";
import { ApiService } from "../../../shared/service/api.service";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";
import { FormsModule } from "@angular/forms";

@Component({
  templateUrl: './rename-pet.dialog.html',
  imports: [
    FormsModule
  ],
  styleUrls: [ './rename-pet.dialog.scss' ]
})
export class RenamePetDialog {

  @Input() pet: MyPetSerializationGroup;

  renaming = false;
  newName = '';

  constructor(
    @Inject(MAT_DIALOG_DATA) public data: any,
    private dialogRef: MatDialogRef<RenamePetDialog>,
    private api: ApiService
  ) {
    this.pet = data.pet;
  }

  doCancel()
  {
    this.dialogRef.close();
  }

  doRename()
  {
    if(this.renaming) return;

    this.renaming = true;

    this.api.patch<{ name: string }>('/pet/' + this.pet.id + '/rename', { name: this.newName }).subscribe({
      next: (r) => {
        this.dialogRef.close({ newPet: { ...this.pet, name: r.data.name, renamingCharges: this.pet.renamingCharges - 1 } });
      },
      error: () => {
        this.renaming = false;
      }
    });
  }

  public static open(matDialog: MatDialog, pet: MyPetSerializationGroup): MatDialogRef<RenamePetDialog>
  {
    return matDialog.open(RenamePetDialog, {
      data: {
        pet: pet
      }
    });
  }

}
