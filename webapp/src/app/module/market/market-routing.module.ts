import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import {MarketComponent} from "./page/market/market.component";
import {BidsComponent} from "./page/bids/bids.component";
import { IncreaseLimitsDialog } from "./page/increase-limits/increase-limits.dialog";

const routes: Routes = [
  { path: '', component: MarketComponent },
  { path: 'bids', component: BidsComponent },
  { path: 'manager', component: IncreaseLimitsDialog }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class MarketRoutingModule { }
