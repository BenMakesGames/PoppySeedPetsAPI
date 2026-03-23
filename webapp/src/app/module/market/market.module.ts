import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import {MarketRoutingModule} from "./market-routing.module";
import {MarketComponent} from "./page/market/market.component";
import {MarkdownModule} from "ngx-markdown";
import { BidsComponent } from './page/bids/bids.component';
import { CreateBidDialog } from './dialog/create-bid/create-bid.dialog';
import {FormsModule} from "@angular/forms";
import { MatDialogModule } from "@angular/material/dialog";
import { ItemNameWithBonusComponent } from "../shared/component/item-name-with-bonus/item-name-with-bonus.component";
import { MoneysComponent } from "../shared/component/moneys/moneys.component";
import { CeilPipe } from "../shared/pipe/ceil.pipe";
import { UrlPaginatorComponent } from "../shared/component/url-paginator/url-paginator.component";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import { ItemSearchComponent } from "../shared/component/item-search/item-search.component";
import { NpcDialogComponent } from "../shared/component/npc-dialog/npc-dialog.component";
import { HasUnlockedFeaturePipe } from "../shared/pipe/has-unlocked-feature.pipe";
import { DateAndTimeComponent } from "../shared/component/date-and-time/date-and-time.component";
import { FindItemByNameComponent } from "../shared/component/find-item-by-name/find-item-by-name.component";
import { IncreaseLimitsDialog } from "./page/increase-limits/increase-limits.dialog";

@NgModule({
  declarations: [
    MarketComponent,
    IncreaseLimitsDialog,
    BidsComponent,
    CreateBidDialog,
  ],
  imports: [
    CommonModule,
    MarketRoutingModule,
    MarkdownModule.forChild(),
    MatDialogModule,
    FormsModule,
    ItemNameWithBonusComponent,
    MoneysComponent,
    CeilPipe,
    UrlPaginatorComponent,
    LoadingThrobberComponent,
    ItemSearchComponent,
    NpcDialogComponent,
    HasUnlockedFeaturePipe,
    DateAndTimeComponent,
    FindItemByNameComponent,
  ]
})
export class MarketModule { }
