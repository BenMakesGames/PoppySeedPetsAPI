import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import {GreenhouseComponent} from "./page/greenhouse/greenhouse.component";

const routes: Routes = [
  { path: '', component: GreenhouseComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class GreenhouseRoutingModule { }
