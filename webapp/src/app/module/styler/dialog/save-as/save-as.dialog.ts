/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
