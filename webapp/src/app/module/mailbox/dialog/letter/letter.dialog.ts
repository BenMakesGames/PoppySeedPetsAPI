/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
