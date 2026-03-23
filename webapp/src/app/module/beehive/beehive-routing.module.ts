import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import {BeehiveComponent} from "./page/beehive/beehive.component";

const routes: Routes = [
  { path: '', component: BeehiveComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class BeehiveRoutingModule { }
