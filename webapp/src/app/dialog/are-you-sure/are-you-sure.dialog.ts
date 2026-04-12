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
import { MAT_DIALOG_DATA, MatDialog, MatDialogConfig, MatDialogRef } from "@angular/material/dialog";
import { MarkdownComponent } from "ngx-markdown";

@Component({
    templateUrl: './are-you-sure.dialog.html',
    imports: [
        MarkdownComponent
    ],
    styleUrls: ['./are-you-sure.dialog.scss']
})
export class AreYouSureDialog {

  title: string;
  description: string;
  yesLabel: string;
  noLabel: string;

  constructor(private dialog: MatDialogRef<AreYouSureDialog>, @Inject(MAT_DIALOG_DATA) private data: any) {
    this.title = data.title;
    this.description = data.description;
    this.yesLabel = data.yesLabel;
    this.noLabel = data.noLabel;
  }

  doYes()
  {
    this.dialog.close(true);
  }

  doNo()
  {
    this.dialog.close(false);
  }

  public static open(matDialog: MatDialog, title: string, description: string, yesLabel: string = 'Yes', noLabel: string = 'No'): MatDialogRef<AreYouSureDialog>
  {
    return matDialog.open(AreYouSureDialog, <MatDialogConfig>{
      data: {
        title: title,
        description: description,
        yesLabel: yesLabel,
        noLabel: noLabel,
      }
    });
  }
}
