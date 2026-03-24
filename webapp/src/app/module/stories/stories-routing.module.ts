import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import { ListComponent } from "./page/list/list.component";
import { ViewComponent } from "./page/view/view.component";

const routes: Routes = [
  { path: '', component: ListComponent },
  { path: ':storyId', component: ViewComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class StoriesRoutingModule { }
