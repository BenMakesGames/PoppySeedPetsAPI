import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import {BasementComponent} from "./page/basement/basement.component";

const routes: Routes = [
  { path: '', component: BasementComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class BasementRoutingModule { }
