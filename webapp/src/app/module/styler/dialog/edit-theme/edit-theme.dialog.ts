import { Component, Inject } from '@angular/core';
import { MyThemeSerializationGroup } from "../../../../model/my-theme.serialization-group";
import { ApiService } from "../../../shared/service/api.service";
import { Subscription } from "rxjs";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";

@Component({
    templateUrl: './edit-theme.dialog.html',
    styleUrls: ['./edit-theme.dialog.scss'],
    standalone: false
})
export class EditThemeDialog {

  renameSubscription = Subscription.EMPTY;
  deleteSubscription = Subscription.EMPTY;
  theme: MyThemeSerializationGroup;
  newName = '';

  constructor(
    private dialogRef: MatDialogRef<EditThemeDialog>,
    private api: ApiService,
    @Inject(MAT_DIALOG_DATA) private data: any
  ) {
    this.theme = data.theme;
    this.newName = this.theme.name;
  }

  doRename()
  {
    this.dialogRef.disableClose = true;

    this.renameSubscription = this.api.patch('/style/' + this.theme.id + '/rename', { name: this.newName }).subscribe({
      next: (r: any) => {
        this.dialogRef.close({ renamed: r.data.name });
      },
      error: () => {
        this.dialogRef.disableClose = false;
      }
    });
  }

  doDelete()
  {
    this.dialogRef.disableClose = true;

    this.deleteSubscription = this.api.del('/style/' + this.theme.id).subscribe({
      next: () => {
        this.dialogRef.close({ deleted: true });
      },
      error: () => {
        this.dialogRef.disableClose = false;
      }
    });
  }

  doClose() {
    this.dialogRef.close();
  }

  public static open(matDialog: MatDialog, theme: MyThemeSerializationGroup)
  {
    return matDialog.open(EditThemeDialog, {
      data: {
        theme: theme
      }
    });
  }

}
