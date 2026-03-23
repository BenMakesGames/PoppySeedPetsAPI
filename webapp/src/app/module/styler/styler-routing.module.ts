import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import { StylerComponent } from "./page/styler/styler.component";
import { ShareComponent } from "./page/share/share.component";
import { ManageComponent } from "./page/manage/manage.component";
import { BuiltInComponent } from "./page/built-in/built-in.component";

const routes: Routes = [
  { path: '', component: StylerComponent },
  { path: 'manage', component: ManageComponent },
  { path: 'find', component: ShareComponent },
  { path: 'builtIn', component: BuiltInComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class StylerRoutingModule { }
