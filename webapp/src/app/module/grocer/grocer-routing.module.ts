import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import { GrocerComponent } from "./page/grocer/grocer.component";

const routes: Routes = [
  { path: '', component: GrocerComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class GrocerRoutingModule { }
