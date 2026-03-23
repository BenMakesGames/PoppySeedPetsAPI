import { Component } from '@angular/core';
import { MatDialog, MatDialogRef } from "@angular/material/dialog";

@Component({
    templateUrl: './ask-to-restart.dialog.html',
    styleUrls: ['./ask-to-restart.dialog.scss'],
    standalone: false
})
export class AskToRestartDialog {

  constructor(private dialogRef: MatDialogRef<AskToRestartDialog>) {
  }

  doUpdateToLatest()
  {
    document.location.reload();
  }

  doAskMeLater()
  {
    this.dialogRef.close();
  }

  public static open(matDialog: MatDialog): MatDialogRef<AskToRestartDialog>
  {
    return matDialog.open(AskToRestartDialog);
  }

}
