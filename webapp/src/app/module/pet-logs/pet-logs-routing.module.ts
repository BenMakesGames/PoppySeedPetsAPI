import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import {PetLogsComponent} from "./page/pet-logs/pet-logs.component";
import { MustBeLoggedInGuard } from "../../guard/must-be-logged-in.guard";
import { PlayerLogsComponent } from "./page/player-logs/player-logs.component";

const routes: Routes = [
  { path: 'pet', component: PetLogsComponent, canActivate: [ MustBeLoggedInGuard ] },
  { path: 'player', component: PlayerLogsComponent, canActivate: [ MustBeLoggedInGuard ] },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class PetLogsRoutingModule { }
