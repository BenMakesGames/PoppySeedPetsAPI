import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import {ParkComponent} from "./page/park/park.component";
import {HistoryComponent} from "./page/history/history.component";

const routes: Routes = [
  { path: '', component: ParkComponent },
  { path: 'history', component: HistoryComponent}
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ParkRoutingModule { }
