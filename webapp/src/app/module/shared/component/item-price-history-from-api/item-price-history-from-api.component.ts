/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {
  Component,
  Input,
  OnChanges,
  OnDestroy,
} from '@angular/core';
import {ApiService} from "../../service/api.service";
import {Subscription} from "rxjs";
import { CommonModule } from "@angular/common";
import { LoadingThrobberComponent } from "../loading-throbber/loading-throbber.component";
import {
  ItemPriceHistoryComponent,
  MarketHistoryResponseData
} from "../item-price-history/item-price-history.component";

@Component({
    selector: 'app-item-price-history-from-api',
    templateUrl: './item-price-history-from-api.component.html',
    imports: [
        CommonModule,
        LoadingThrobberComponent,
        ItemPriceHistoryComponent
    ],
    styleUrls: ['./item-price-history-from-api.component.scss']
})
export class ItemPriceHistoryFromApiComponent implements OnChanges, OnDestroy {

  @Input() itemId: number;

  marketHistoryAjax = Subscription.EMPTY;
  data: MarketHistoryResponseData|null = null;

  constructor(private api: ApiService) { }

  ngOnDestroy(): void {
    this.marketHistoryAjax.unsubscribe();
  }

  ngOnChanges()
  {
    this.marketHistoryAjax = this.api.get<MarketHistoryResponseData>('/market/history/' + this.itemId).subscribe({
      next: r => {
        this.data = r.data;
      }
    });
  }
}
