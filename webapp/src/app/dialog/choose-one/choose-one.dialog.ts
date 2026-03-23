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