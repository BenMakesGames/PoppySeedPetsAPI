import {Component, Inject} from '@angular/core';
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";

@Component({
    templateUrl: './mission-results.dialog.html',
    styleUrls: ['./mission-results.dialog.scss'],
    standalone: false
})
export class MissionResultsDialog {

  title: string;
  message: string;

  constructor(
    @Inject(MAT_DIALOG_DATA) data: any,
    private dialogRef: MatDialogRef<MissionResultsDialog>
  ) {
    this.message = data.message;
    this.title = !data.title?.trim() ? 'Progress!' : data.title.trim();
  }

  doOk()
  {
    this.dialogRef.close();
  }

  static open(matDialog: MatDialog, title: string, message: string): MatDialogRef<MissionResultsDialog>
  {
    return matDialog.open(MissionResultsDialog, {
      data: {
        title: title,
        message: message,
      }
    });
  }
}
