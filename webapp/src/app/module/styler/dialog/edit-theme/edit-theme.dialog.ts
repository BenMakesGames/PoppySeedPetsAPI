/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
