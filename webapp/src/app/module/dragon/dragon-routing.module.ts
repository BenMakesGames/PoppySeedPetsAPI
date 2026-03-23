import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import {DragonComponent} from "./page/dragon/dragon.component";

const routes: Routes = [
  { path: '', component: DragonComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class DragonRoutingModule { }
