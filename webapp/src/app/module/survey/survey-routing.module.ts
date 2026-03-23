import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import { SurveyComponent } from "./page/survey/survey.component";

const routes: Routes = [
  { path: ':guid', component: SurveyComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class SurveyRoutingModule { }
