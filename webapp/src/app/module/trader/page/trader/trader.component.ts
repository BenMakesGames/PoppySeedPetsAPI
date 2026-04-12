/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, OnDestroy, OnInit} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {ApiResponseModel} from "../../../../model/api-response.model";
import { BehaviorSubject, Subscription } from "rxjs";
import { ConfirmTradeQuantityDialog } from "../../dialog/confirm-trade-quantity/confirm-trade-quantity.dialog";
import { TradeGroup } from "../../model/trade-group.serialization-group";
import { TraderOffer } from "../../model/trader-offer.serialization-group";
import { TradeOffersSerializationGroup } from "../../model/trade-offers.serialization-group";
import { MatDialog } from "@angular/material/dialog";
import { NavService } from "../../../../service/nav.service";

@Component({
    selector: 'app-trader',
    templateUrl: './trader.component.html',
    styleUrls: ['./trader.component.scss'],
    standalone: false
})
export class TraderComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Trader' };

  trading = false;
  trades: TradeGroup[];
  favoriteTradeIds = new BehaviorSubject<string[]>([]);
  favoriteTrades: TraderOffer[];
  filteredFavoriteTrades: TraderOffer[];
  filteredTrades: TradeGroup[];
  filters: TradeFilters = { onlyAvailable: false, text: '' };
  trader: Trader;
  traderColors: any;
  dialog = 'Greetings, human! What are you looking for, today?';
  filterIsSticky = false;

  traderAjax = Subscription.EMPTY;
  favoriteIdsSubscription = Subscription.EMPTY;

  constructor(
    private api: ApiService, private matDialog: MatDialog,
    private navService: NavService
  ) {
  }

  ngOnInit() {
    const now = new Date();
    const utcMonth = now.getUTCMonth() + 1;
    const utcDay = now.getUTCDate();

    if(utcMonth === 5 && utcDay === 4)
      this.dialog = 'Greetings, human! "May the 4th be with you!"\n\nDid I say it right? It\'s hard to keep up with all the little holidays your species observes.\n\nThat being said, I have a seasonal item on offer today! If you\'re unsure how to get a Photon, I believe aging a Tiny Black Hole will cause it to emit some.';

    this.traderAjax = this.api.get<TraderSerializationGroup>('/trader').subscribe(
      (r: ApiResponseModel<TraderSerializationGroup>) => {
        this.trades = r.data.trades;
        this.filteredTrades = this.filterTradeGroups(r.data.trades, this.filters);
        this.favoriteTradeIds.next(r.data.favorites);
        this.trader = r.data.trader;
        this.traderColors = {
          colorA: this.trader.colorA,
          colorB: this.trader.colorB,
          colorC: this.trader.colorC,
        };
      }
    );

    this.favoriteIdsSubscription = this.favoriteTradeIds.subscribe(favorites => {
      this.favoriteTrades = this.trades ? this.trades.flatMap(g => g.trades).filter(t => favorites.indexOf(t.id) >= 0) : [];
      this.filteredFavoriteTrades = this.filterTrades(this.favoriteTrades, this.filters);
    });
  }

  doToggleUnavailableTrades()
  {
    this.filters.onlyAvailable = !this.filters.onlyAvailable;
    this.afterUpdateFilters();
  }

  filterTrades(trades: TraderOffer[], filters: TradeFilters): TraderOffer[]
  {
    let filteredTrades = [ ...trades ];

    if(filters.onlyAvailable)
      filteredTrades = filteredTrades.filter(t => t.canMakeExchange);

    if(filters.text)
      filteredTrades = filteredTrades.filter(t => TraderComponent.tradeContainsText(t, filters.text));

    return filteredTrades;
  }

  filterTradeGroups(trades: TradeGroup[], filters: TradeFilters): TradeGroup[]
  {
    let filtered = trades.map(g => { return { ...g }; });

    for(let group of filtered)
      group.trades = this.filterTrades(group.trades, filters);

    return filtered.filter(t => t.trades.length > 0);
  }

  afterUpdateFilters()
  {
    this.filteredTrades = this.filterTradeGroups(this.trades, this.filters);
    this.filteredFavoriteTrades = this.filterTrades(this.favoriteTrades, this.filters);
  }

  ngOnDestroy(): void {
    this.traderAjax.unsubscribe();
    this.favoriteIdsSubscription.unsubscribe();
  }

  doTrade(offer: TraderOffer)
  {
    ConfirmTradeQuantityDialog.open(this.matDialog, offer, this.favoriteTradeIds).afterClosed().subscribe(
      (r: TradeOffersSerializationGroup|null) => {
        if(r)
        {
          this.dialog = offer.comment;
          this.trades = r.trades;
          this.afterUpdateFilters();
          this.favoriteTrades = r.trades.flatMap(g => g.trades).filter(t => this.favoriteTradeIds.value.indexOf(t.id) >= 0);

          if(r.message)
            this.dialog += "\n\n" + r.message;
        }
      }
    );
  }

  doSetSticky(isSticky: boolean)
  {
    this.filterIsSticky = isSticky;
    this.navService.disableHeaderShadow.next(isSticky);
  }

  private static tradeContainsText(trade: TraderOffer, text: string): boolean
  {
    const searchText = text.trim().toLowerCase();

    return trade.cost.some(c => (c.type === 'item' && c.item.name.toLowerCase().includes(searchText)) || c.type.toLowerCase().includes(searchText)) ||
      trade.yield.some(y => (y.type === 'item' && y.item.name.toLowerCase().includes(searchText)) || y.type.toLowerCase().includes(searchText)) ||

      trade.cost.some(c => c.quantity == parseInt(searchText)) ||
      trade.yield.some(y => y.quantity == parseInt(searchText));
  }
}

interface Trader
{
  name: string;
  colorA: string;
  colorB: string;
  colorC: string;
}

interface TraderSerializationGroup
{
  trades: TradeGroup[];
  trader: Trader;
  favorites: string[];
}

interface TradeFilters
{
  onlyAvailable: boolean;
  text: string;
}