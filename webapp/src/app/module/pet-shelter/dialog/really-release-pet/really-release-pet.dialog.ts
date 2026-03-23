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
