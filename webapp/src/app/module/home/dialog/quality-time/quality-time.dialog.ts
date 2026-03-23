import { Component, OnInit } from '@angular/core';
import { MatDialog, MatDialogRef } from "@angular/material/dialog";
import { CommonModule } from "@angular/common";
import { LoadingThrobberComponent } from "../../../shared/component/loading-throbber/loading-throbber.component";
import { MarkdownComponent } from "ngx-markdown";
import { ApiService } from "../../../shared/service/api.service";

@Component({
    templateUrl: './quality-time.dialog.html',
    styleUrls: ['./quality-time.dialog.scss'],
    imports: [CommonModule, LoadingThrobberComponent, MarkdownComponent]
})
export class QualityTimeDialog implements OnInit {

  qualityTimeText: string|null = null;

  constructor(
    private dialogRef: MatDialogRef<QualityTimeDialog>,
    private apiService: ApiService
  ) { }

  ngOnInit(): void {
    this.apiService.post<{ message: string }>('/house/doQualityTime').subscribe({
      next: r => {
        this.qualityTimeText = r.data.message;
      },
      error: () => {
        this.qualityTimeText = 'Oh, goodness! Some kind of error happened. Maybe try again??';
      }
    })
  }

  doClose()
  {
    this.dialogRef.close();
  }

  public static show(matDialog: MatDialog)
  {
    matDialog.open(QualityTimeDialog);
  }
}
