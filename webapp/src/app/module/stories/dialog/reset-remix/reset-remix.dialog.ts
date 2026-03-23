import {Component, Inject} from '@angular/core';
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";
import { ApiService } from "../../../shared/service/api.service";
import { Subscription } from "rxjs";

@Component({
    templateUrl: './reset-remix.dialog.html',
    styleUrls: ['./reset-remix.dialog.scss'],
    standalone: false
})
export class ResetRemixDialog {

  resetSubscription = Subscription.EMPTY;
  storyId: string;

  constructor(
    @Inject(MAT_DIALOG_DATA) data: any,
    private readonly dialogRef: MatDialogRef<ResetRemixDialog>,
    private readonly apiService: ApiService
  ) {
    this.storyId = data.storyId;
  }

  doOk()
  {
    if(!this.resetSubscription.closed)
      return;

    this.resetSubscription = this.apiService.post(`/starKindred/restartRemix/${this.storyId}`, {}).subscribe({
      next: () => {
        this.dialogRef.close(true);
      }
    });
  }

  static open(matDialog: MatDialog, storyId: string): MatDialogRef<ResetRemixDialog>
  {
    return matDialog.open(ResetRemixDialog, {
      data: {
        storyId: storyId,
      },
      disableClose: true
    });
  }
}
