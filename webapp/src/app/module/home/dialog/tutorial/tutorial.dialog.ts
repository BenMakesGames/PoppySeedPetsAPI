import { Component, OnInit } from '@angular/core';
import {MessagesService} from "../../../../service/messages.service";
import { MatDialog, MatDialogRef } from "@angular/material/dialog";

@Component({
  templateUrl: './tutorial.dialog.html',
  styleUrls: ['./tutorial.dialog.scss'],
  standalone: true,
})
export class TutorialDialog {

  constructor(
    private dialogRef: MatDialogRef<TutorialDialog>, private messagesService: MessagesService
  ) { }

  doClose(denyEverything: boolean)
  {
    this.dialogRef.close();

    if(denyEverything)
      this.messagesService.addGenericMessage('Whaaaaat? No! I-- pfsh! What? That-- that\'s just silly! You\'re so _silly_... >_>');
  }

  public static show(matDialog: MatDialog)
  {
    matDialog.open(TutorialDialog);
  }

}
