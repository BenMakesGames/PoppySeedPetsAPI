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
