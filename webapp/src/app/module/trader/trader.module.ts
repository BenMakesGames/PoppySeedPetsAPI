/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
