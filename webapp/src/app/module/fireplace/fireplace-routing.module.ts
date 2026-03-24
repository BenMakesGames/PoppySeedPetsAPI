import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import {FireplaceComponent} from "./page/fireplace/fireplace.component";

const routes: Routes = [
  { path: '', component: FireplaceComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class FireplaceRoutingModule { }
