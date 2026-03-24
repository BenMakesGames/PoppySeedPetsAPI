import {Component, Inject, OnInit} from '@angular/core';
import {MyLetterSerializationGroup} from "../../../../model/my-letter.serialization-group";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";

@Component({
    templateUrl: './letter.dialog.html',
    styleUrls: ['./letter.dialog.scss'],
    standalone: false
})
export class LetterDialog implements OnInit {

  letter: MyLetterSerializationGroup;

  constructor(
    private dialogRef: MatDialogRef<LetterDialog>,
    @Inject(MAT_DIALOG_DATA) private data: any,
  ) {
    this.letter = data.letter;
  }

  ngOnInit(): void {
  }

  public static open(matDialog: MatDialog, letter: MyLetterSerializationGroup)
  {
    return matDialog.open(LetterDialog, { data: { letter: letter }});
  }
}
