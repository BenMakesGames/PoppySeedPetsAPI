import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import {HollowEarthComponent} from "./page/hollow-earth/hollow-earth.component";
import { TradeDepotComponent } from "./page/trade-depot/trade-depot.component";

const routes: Routes = [
  { path: '', component: HollowEarthComponent },
  { path: 'tradeDepot', component: TradeDepotComponent }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class HollowEarthRoutingModule { }
