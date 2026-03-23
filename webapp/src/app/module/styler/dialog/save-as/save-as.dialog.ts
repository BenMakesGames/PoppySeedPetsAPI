import { Component, OnInit } from '@angular/core';
import { ApiService } from "../../../shared/service/api.service";
import { Subscription } from "rxjs";
import { MatDialog, MatDialogRef } from "@angular/material/dialog";

@Component({
    templateUrl: './save-as.dialog.html',
    styleUrls: ['./save-as.dialog.scss'],
    standalone: false
})
export class SaveAsDialog implements OnInit {

  saveSubscription = Subscription.EMPTY;
  name = '';

  constructor(private dialogRef: MatDialogRef<SaveAsDialog>, private api: ApiService) { }

  ngOnInit(): void {
  }

  doClose()
  {
    this.dialogRef.close();
  }

  doSave()
  {
    this.dialogRef.disableClose = true;
    this.saveSubscription = this.api.post('/style', { name: this.name }).subscribe({
      next: () => {
        this.dialogRef.close(true);
      },
      error: () => {
        this.dialogRef.disableClose = false;
      }
    });
  }

  public static open(matDialog: MatDialog)
  {
    return matDialog.open(SaveAsDialog);
  }

}
