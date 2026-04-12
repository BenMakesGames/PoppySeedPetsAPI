/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, OnDestroy } from '@angular/core';
import { ApiService } from "../../../shared/service/api.service";
import { Subscription } from "rxjs";
import { HollowEarthTradeSerializationGroup } from "../../model/hollow-earth-trade.serialization-group";
import { ItemDetailsDialog } from "../../../../dialog/item-details/item-details.dialog";
import { ConfirmTradeQuantityDialog } from "../../dialog/confirm-trade-quantity/confirm-trade-quantity.dialog";
import { MatDialog } from "@angular/material/dialog";

@Component({
    templateUrl: './trade-depot.component.html',
    styleUrls: ['./trade-depot.component.scss'],
    standalone: false
})
export class TradeDepotComponent implements OnDestroy {

  pageMeta = { title: 'Hollow Earth - Trade Depot' };

  tradesLoading = Subscription.EMPTY;
  trades: HollowEarthTradeSerializationGroup[]|null = null;

  constructor(
    private api: ApiService, private matDialog: MatDialog,
  ) {
    this.tradesLoading = this.api.get<HollowEarthTradeSerializationGroup[]>('/hollowEarth/trades').subscribe({
      next: r => {
        this.trades = r.data;
      }
    })
  }

  ngOnDestroy(): void {
    this.tradesLoading.unsubscribe();
  }

  public doTrade(trade: HollowEarthTradeSerializationGroup)
  {
    ConfirmTradeQuantityDialog.open(this.matDialog, trade).afterClosed().subscribe({
      next: v => {
        if(v)
        {
          this.trades = v;
        }
      }
    });
  }

  doViewItem(itemName: string)
  {
    ItemDetailsDialog.open(this.matDialog, itemName);
  }
}
