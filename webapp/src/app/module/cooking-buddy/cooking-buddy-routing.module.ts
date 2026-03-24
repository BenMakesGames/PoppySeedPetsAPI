import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import { CookingBuddyComponent } from "./page/cooking-buddy/cooking-buddy.component";

const routes: Routes = [
  { path: '', component: CookingBuddyComponent },
];

@NgModule({
  imports: [
    RouterModule.forChild(routes),
  ],
  exports: [RouterModule],
})
export class CookingBuddyRoutingModule { }
