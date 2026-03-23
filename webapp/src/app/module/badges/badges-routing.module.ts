import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import { BadgesComponent } from "./page/badges/badges.component";
import { ClaimedComponent } from "./page/claimed/claimed.component";
import { ShowcaseComponent } from "./page/showcase/showcase.component";
import { PetBadgesComponent } from "./page/pet-badges/pet-badges.component";

const routes: Routes = [
  { path: '', component: BadgesComponent },
  { path: 'claimed', component: ClaimedComponent },
  { path: 'showcase', component: ShowcaseComponent },
  { path: 'petBadges', component: PetBadgesComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class BadgesRoutingModule { }
