/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, Inject, Input} from '@angular/core';
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";
import { MarkdownComponent } from "ngx-markdown";
import { CommonModule } from "@angular/common";

@Component({
    templateUrl: './choose-one.dialog.html',
    imports: [
        MarkdownComponent,
        CommonModule
    ],
    styleUrls: ['./choose-one.dialog.scss']
})
export class ChooseOneDialog {

  title: string;
  description: string;

  @Input() choices: ChoiceModel[];

  constructor(private dialog: MatDialogRef<ChooseOneDialog>, @Inject(MAT_DIALOG_DATA) private data: any) {
    this.title = data.title;
    this.description = data.description;
    this.choices = data.choices;
  }

  doSelect(choice: ChoiceModel)
  {
    this.dialog.close(choice);
  }

  public static open(matDialog: MatDialog, title: string, description: string, choices: ChoiceModel[]): MatDialogRef<ChooseOneDialog>
  {
    return matDialog.open(ChooseOneDialog, {
      data: {
        title: title,
        description: description,
        choices: choices,
      }
    });
  }

}

export interface ChoiceModel
{
  label: string,
  value: any
}