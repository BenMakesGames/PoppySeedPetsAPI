import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import {TraderComponent} from "./page/trader/trader.component";
import {DescribeTraderCostOrYieldComponent} from "./component/describe-trader-cost-or-yield/describe-trader-cost-or-yield.component";
import {TraderRoutingModule} from "./trader-routing.module";
import {MarkdownModule} from "ngx-markdown";
import { ConfirmTradeQuantityDialog } from "./dialog/confirm-trade-quantity/confirm-trade-quantity.dialog";
import { FormsModule } from "@angular/forms";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import { NpcDialogComponent } from "../shared/component/npc-dialog/npc-dialog.component";
import { MoneysComponent } from "../shared/component/moneys/moneys.component";
import { RecyclingPointsComponent } from "../shared/component/recycling-points/recycling-points.component";
import { DescribeYieldComponent } from "./component/describe-yield/describe-yield.component";
import { CostOrYieldTitlePipe } from "./pipe/cost-or-yield-title.pipe";
import { ObserveOnScreenDirective } from "../shared/directive/observe-on-screen.directive";

@NgModule({
  declarations: [
    TraderComponent,
    DescribeTraderCostOrYieldComponent,
    ConfirmTradeQuantityDialog,
    DescribeYieldComponent,
    CostOrYieldTitlePipe,
  ],
  exports: [
    DescribeTraderCostOrYieldComponent
  ],
  imports: [
    CommonModule,
    TraderRoutingModule,
    FormsModule,
    MarkdownModule.forChild(),
    LoadingThrobberComponent,
    NpcDialogComponent,
    MoneysComponent,
    RecyclingPointsComponent,
    ObserveOnScreenDirective,
  ]
})
export class TraderModule { }
