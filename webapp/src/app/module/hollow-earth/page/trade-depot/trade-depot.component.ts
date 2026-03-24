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
