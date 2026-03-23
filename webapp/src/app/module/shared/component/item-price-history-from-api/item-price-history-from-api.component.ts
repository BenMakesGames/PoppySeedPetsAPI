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
