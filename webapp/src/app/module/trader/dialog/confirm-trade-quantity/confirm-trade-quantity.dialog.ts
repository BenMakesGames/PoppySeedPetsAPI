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
import { ApiResponseModel } from "../../../../model/api-response.model";
import { TradeOffersSerializationGroup } from "../../model/trade-offers.serialization-group";
import { TraderOffer } from "../../model/trader-offer.serialization-group";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";
import { BehaviorSubject, Subscription } from "rxjs";

@Component({
    templateUrl: './confirm-trade-quantity.dialog.html',
    styleUrls: ['./confirm-trade-quantity.dialog.scss'],
    standalone: false
})
export class ConfirmTradeQuantityDialog {

  quantity = 1;
  trading = Subscription.EMPTY;
  favoriting = Subscription.EMPTY;

  trade: TraderOffer;
  favorites: BehaviorSubject<string[]>;
  isFavorite: boolean;

  constructor(
    @Inject(MAT_DIALOG_DATA) private data: any,
    private dialogRef: MatDialogRef<ConfirmTradeQuantityDialog>,
    private api: ApiService
  ) {
    this.trade = data.trade;
    this.favorites = data.favorites;
    this.isFavorite = this.favorites.value.some(f => f === this.trade.id);

    if(this.trade.canMakeExchange == 0)
      this.quantity = 0;
  }

  doCancel()
  {
    this.dialogRef.close();
  }

  doFavorite()
  {
    if(!this.favoriting.closed)
      return;

    if(this.isFavorite)
    {
      this.favoriting = this.api.del('/trader/' + this.trade.id + '/favorite').subscribe({
        next: () => {
          this.favorites.next(this.favorites.value.filter(f => f !== this.trade.id));
          this.isFavorite = false;
          this.updateCanCloseDialog();
        },
        error: () => {
          this.updateCanCloseDialog();
        }
      });
    }
    else
    {
      this.favoriting = this.api.post('/trader/' + this.trade.id + '/favorite').subscribe({
        next: () => {
          this.favorites.next(this.favorites.value.concat(this.trade.id));
          this.isFavorite = true;
          this.updateCanCloseDialog();
        },
        error: () => {
          this.updateCanCloseDialog();
        }
      });
    }

    this.updateCanCloseDialog();
  }

  doTrade()
  {
    if(!this.trading.closed || !this.trade.canMakeExchange) return;

    this.trading = this.api.post('/trader/' + this.trade.id + '/exchange', { quantity: this.quantity }).subscribe({
      next: (r: ApiResponseModel<TradeOffersSerializationGroup>) => {
        this.dialogRef.close(r.data);
        this.updateCanCloseDialog();
      },
      error: () => {
        this.updateCanCloseDialog();
      }
    });

    this.updateCanCloseDialog();
  }

  private updateCanCloseDialog()
  {
    this.dialogRef.disableClose = !this.trading.closed && !this.favoriting.closed;
  }

  public static open(matDialog: MatDialog, trade: TraderOffer, favorites: BehaviorSubject<string[]>): MatDialogRef<ConfirmTradeQuantityDialog>
  {
    return matDialog.open(ConfirmTradeQuantityDialog, {
      data: {
        trade: trade,
        favorites: favorites
      }
    });
  }
}
