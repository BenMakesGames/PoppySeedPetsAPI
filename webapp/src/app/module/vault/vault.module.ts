import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { VaultComponent } from "./page/vault/vault.component";
import { VaultRoutingModule } from "./vault-routing.module";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import { UrlPaginatorComponent } from "../shared/component/url-paginator/url-paginator.component";
import { MoneysComponent } from "../shared/component/moneys/moneys.component";

@NgModule({
  declarations: [
    VaultComponent
  ],
  imports: [
    CommonModule,
    VaultRoutingModule,
    LoadingThrobberComponent,
    UrlPaginatorComponent,
    MoneysComponent,
  ]
})
export class VaultModule { }
