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
