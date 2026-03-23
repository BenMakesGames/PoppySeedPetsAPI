import { Component} from '@angular/core';
import { MatDialog, MatDialogRef } from "@angular/material/dialog";

@Component({
    templateUrl: './enter-passphrase.dialog.html',
    styleUrls: ['./enter-passphrase.dialog.scss'],
    standalone: false
})
export class EnterPassphraseDialog {

  passphrase = '';

  constructor(private dialogRef: MatDialogRef<EnterPassphraseDialog>) { }

  doSubmit()
  {
    this.dialogRef.close(this.passphrase.trim());
  }

  public static open(matDialog: MatDialog): MatDialogRef<EnterPassphraseDialog>
  {
    return matDialog.open(EnterPassphraseDialog);
  }
}
