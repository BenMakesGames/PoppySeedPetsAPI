import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import {TrickOrTreaterComponent} from "./page/trick-or-treater/trick-or-treater.component";

const routes: Routes = [
  { path: 'trickOrTreater', component: TrickOrTreaterComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class HalloweenRoutingModule { }
