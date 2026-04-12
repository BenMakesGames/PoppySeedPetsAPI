/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, EventEmitter, Inject, Output} from '@angular/core';
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";

@Component({
    templateUrl: './describe-calendar-day.dialog.html',
    styleUrls: ['./describe-calendar-day.dialog.scss'],
    standalone: false
})
export class DescribeCalendarDayDialog {

  @Output() reloadArticles = new EventEmitter();

  date: string;
  holidays: string[];

  constructor(
    private dialogRef: MatDialogRef<DescribeCalendarDayDialog>,
    @Inject(MAT_DIALOG_DATA) private data: any
  ) {
    this.date = data.date;
    this.holidays = data.holidays;
  }

  doClose()
  {
    this.dialogRef.close();
  }

  public static open(matDialog: MatDialog, date: string, holidays: string[]): MatDialogRef<DescribeCalendarDayDialog>
  {
    return matDialog.open(DescribeCalendarDayDialog, {
      maxWidth: '640px',
      data: {
        date: date,
        holidays: holidays
      }
    });
  }
}
