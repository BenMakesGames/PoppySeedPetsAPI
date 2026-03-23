import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import { FloristComponent } from "./page/florist/florist.component";
import { RecyclingGameComponent } from "./page/recycling-game/recycling-game.component";

const routes: Routes = [
  { path: '', component: FloristComponent },
  { path: 'satyrDice', component: RecyclingGameComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class FloristRoutingModule { }
