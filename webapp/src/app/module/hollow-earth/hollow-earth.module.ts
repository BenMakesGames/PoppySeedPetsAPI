import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import {HollowEarthRoutingModule} from "./hollow-earth-routing.module";
import { TileDetailsDialog } from "./dialog/tile-details/tile-details.dialog";
import { SelectTileDialog } from "./dialog/select-tile/select-tile.dialog";
import { TileGoodsComponent } from './component/tile-goods/tile-goods.component';
import { ChangeGoodsDialog } from './dialog/change-goods/change-goods.dialog';
import { FormsModule } from "@angular/forms";
import { DescribeTradeDepotCostComponent } from './component/describe-trade-depot-cost/describe-trade-depot-cost.component';
import { HollowEarthComponent } from "./page/hollow-earth/hollow-earth.component";
import { TradeDepotComponent } from "./page/trade-depot/trade-depot.component";
import { MarkdownModule } from "ngx-markdown";
import { ConfirmTradeQuantityDialog } from "./dialog/confirm-trade-quantity/confirm-trade-quantity.dialog";
import { InventoryItemComponent } from "../shared/component/inventory-item/inventory-item.component";
import { HasUnlockedFeaturePipe } from "../shared/pipe/has-unlocked-feature.pipe";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import { MoneysComponent } from "../shared/component/moneys/moneys.component";
import { PetAppearanceComponent } from "../shared/component/pet-appearance/pet-appearance.component";

@NgModule({
  declarations: [
    TileDetailsDialog,
    SelectTileDialog,
    TileGoodsComponent,
    ChangeGoodsDialog,
    DescribeTradeDepotCostComponent,
    HollowEarthComponent,
    TradeDepotComponent,
    ConfirmTradeQuantityDialog,
  ],
  exports: [
    TileGoodsComponent
  ],
  imports: [
    CommonModule,
    HollowEarthRoutingModule,
    FormsModule,
    MarkdownModule,
    InventoryItemComponent,
    HasUnlockedFeaturePipe,
    LoadingThrobberComponent,
    MoneysComponent,
    PetAppearanceComponent
  ]
})
export class HollowEarthModule { }
