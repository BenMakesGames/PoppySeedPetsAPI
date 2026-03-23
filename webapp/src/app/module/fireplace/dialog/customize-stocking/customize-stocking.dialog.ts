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
