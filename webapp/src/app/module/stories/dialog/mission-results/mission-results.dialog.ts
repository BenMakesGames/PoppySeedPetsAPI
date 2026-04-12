/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
