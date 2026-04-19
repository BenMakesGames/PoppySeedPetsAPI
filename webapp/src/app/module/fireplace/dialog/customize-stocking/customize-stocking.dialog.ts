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
import {ApiService} from "../../../shared/service/api.service";
import {Subscription} from "rxjs";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";

@Component({
    selector: 'app-customize-stocking',
    templateUrl: './customize-stocking.dialog.html',
    styleUrls: ['./customize-stocking.dialog.scss'],
    standalone: false
})
export class CustomizeStockingDialog implements OnInit {

  AVAILABLE_STOCKINGS = [
    'tasseled',
    'fluffed',
    'snowflaked',
    'forest',
    'cow',
    'eye',
    'holly',
  ];

  stockingSubscription = Subscription.EMPTY;
  stocking: StockingDeetsModel;

  constructor(
    private dialogRef: MatDialogRef<CustomizeStockingDialog>,
    private api: ApiService,
    @Inject(MAT_DIALOG_DATA) private data,
  ) {
    this.stocking = { ...data.stocking };
  }

  ngOnInit(): void {
  }

  doSelectAppearance(image: string)
  {
    this.stocking.appearance = image;
  }

  doCancel()
  {
    this.dialogRef.close();
  }

  doSave()
  {
    if(!this.stockingSubscription.closed)
      return;

    const data = {
      appearance: this.stocking.appearance,
      colorA: this.stocking.colorA,
      colorB: this.stocking.colorB
    };

    this.stockingSubscription = this.api.patch('/fireplace/stocking', data).subscribe(
      () => {
        this.dialogRef.close(this.stocking);
      }
    );
  }

  public static open(matDialog: MatDialog, stockingDeets: StockingDeetsModel): MatDialogRef<CustomizeStockingDialog>
  {
    return matDialog.open(CustomizeStockingDialog, {
      disableClose: true,
      maxWidth: 'min(80vw, 460px)',
      data: {
        stocking: stockingDeets
      }
    });
  }
}

interface StockingDeetsModel
{
  appearance: string;
  colorA: string;
  colorB: string;
}
