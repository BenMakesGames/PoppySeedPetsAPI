import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import {VaultComponent} from "./page/vault/vault.component";

const routes: Routes = [
  { path: '', component: VaultComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class VaultRoutingModule { }
