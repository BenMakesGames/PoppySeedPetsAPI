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
