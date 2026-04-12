/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, Inject} from '@angular/core';
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";
import { MyPetSerializationGroup } from "../../../../model/my-pet/my-pet.serialization-group";

@Component({
    templateUrl: './really-release-pet.dialog.html',
    styleUrls: ['./really-release-pet.dialog.scss'],
    standalone: false
})
export class ReallyReleasePetDialog {

  pet: MyPetSerializationGroup;
  guiltText: string;

  constructor(
    @Inject(MAT_DIALOG_DATA) private data: any,
    private dialog: MatDialogRef<ReallyReleasePetDialog>
  )
  {
    this.pet = data.pet;

    if(this.pet.level >= 20)
      this.guiltText = 'Wait, really? This is ' + this.pet.name + ' we\'re talking about, here!';
    else if(this.pet.level >= 10)
      this.guiltText = this.pet.name + '? Beautiful, level-' + this.pet.level + ' ' + this.pet.name + '? Are you sure?';
    else if(this.pet.level >= 5)
      this.guiltText = this.pet.name + '? Is it really time to part ways?';
    else
      this.guiltText = this.pet.name + '! I hardly knew ye!';
  }

  doConfirm()
  {
    this.dialog.close(true);
  }

  doCancel()
  {
    this.dialog.close(false);
  }

  public static open(matDialog: MatDialog, pet): MatDialogRef<ReallyReleasePetDialog>
  {
    return matDialog.open(ReallyReleasePetDialog, {
      data: {
        pet: pet
      }
    });
  }
}
