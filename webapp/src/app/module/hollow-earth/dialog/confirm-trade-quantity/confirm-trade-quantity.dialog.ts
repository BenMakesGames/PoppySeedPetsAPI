import {Component, Inject} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import { HollowEarthTradeSerializationGroup } from "../../model/hollow-earth-trade.serialization-group";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";

@Component({
    templateUrl: './confirm-trade-quantity.dialog.html',
    styleUrls: ['./confirm-trade-quantity.dialog.scss'],
    standalone: false
})
export class ConfirmTradeQuantityDialog {

  quantity = 1;
  trading = false;

  trade: HollowEarthTradeSerializationGroup;

  constructor(
    @Inject(MAT_DIALOG_DATA) private data: any,
    private dialogRef: MatDialogRef<ConfirmTradeQuantityDialog>,
    private api: ApiService
  ) {
    this.trade = data.trade;

    if(this.trade.maxQuantity == 0)
      this.quantity = 0;
  }

  doCancel()
  {
    this.dialogRef.close();
  }

  doTrade()
  {
    if(this.trading || this.trade.maxQuantity == 0) return;

    this.trading = true;

    this.api.post<HollowEarthTradeSerializationGroup[]>('/hollowEarth/trades/' + this.trade.id + '/exchange', { quantity: this.quantity }).subscribe({
      next: r => {
        this.dialogRef.close(r.data);
      },
      error: () => {
        this.trading = false;
      }
    });
  }

  public static open(matDialog: MatDialog, trade: HollowEarthTradeSerializationGroup): MatDialogRef<ConfirmTradeQuantityDialog>
  {
    return matDialog.open(ConfirmTradeQuantityDialog, {
      data: {
        trade: trade
      }
    });
  }
}
