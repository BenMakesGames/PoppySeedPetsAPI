/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, Inject, OnInit } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";
import { SelectPetComponent } from "../../module/shared/component/select-pet/select-pet.component";

@Component({
    templateUrl: './select-pet.dialog.html',
    imports: [
        SelectPetComponent
    ],
    styleUrls: ['./select-pet.dialog.scss']
})
export class SelectPetDialog implements OnInit {

  additionalFilters: any;

  constructor(
    private matDialogRef: MatDialogRef<SelectPetDialog>,
    @Inject(MAT_DIALOG_DATA) data: any,
  ) {
    this.additionalFilters = data.additionalFilters;
  }

  ngOnInit() {
  }

  doSelect(pet)
  {
    if(pet !== null)
      this.matDialogRef.close(pet);
  }

  public static open(matDialog: MatDialog, additionalFilters: any = null): MatDialogRef<SelectPetDialog>
  {
    return matDialog.open(SelectPetDialog, {
      data: {
        additionalFilters: additionalFilters
      }
    });
  }
}
