import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import { NewsComponent } from "./page/news/news.component";

const routes: Routes = [
  { path: '', component: NewsComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class NewsRoutingModule { }
