import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import { ZoologistComponent } from "./page/zoologist/zoologist.component";
import { ShowSpeciesComponent } from "./page/show-species/show-species.component";

const routes: Routes = [
  { path: '', component: ZoologistComponent },
  { path: 'showSpecies', component: ShowSpeciesComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ZoologistRoutingModule { }
