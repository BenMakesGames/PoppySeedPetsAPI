/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
