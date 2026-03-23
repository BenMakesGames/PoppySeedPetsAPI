import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import {PetShelterComponent} from "./page/pet-shelter/pet-shelter.component";
import {PickUpComponent} from "./page/pet-shelter/pick-up/pick-up.component";

const routes: Routes = [
  { path: '', component: PetShelterComponent },
  { path: 'daycare', component: PickUpComponent}
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class PetShelterRoutingModule { }
