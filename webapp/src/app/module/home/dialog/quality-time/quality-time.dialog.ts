/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
