import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import { FieldGuideComponent } from "./page/field-guide/field-guide.component";

const routes: Routes = [
  { path: '', component: FieldGuideComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class FieldGuideRoutingModule { }
