import {RouterModule, Routes} from "@angular/router";
import {NgModule} from "@angular/core";
import {MuseumComponent} from "./page/museum/museum.component";
import {MuseumTopDonatorsComponent} from "./page/museum/museum-top-donators/museum-top-donators.component";
import {MuseumDonateComponent} from "./page/museum/museum-donate/museum-donate.component";
import {MuseumUpgradeComponent} from "./page/museum/museum-upgrade/museum-upgrade.component";
import {MuseumWingComponent} from "./page/museum/museum-wing/museum-wing.component";
import { GiftShopComponent } from "./page/gift-shop/gift-shop.component";

const routes: Routes = [
  { path: '', component: MuseumComponent },
  { path: 'topDonors', component: MuseumTopDonatorsComponent },
  { path: 'donate', component: MuseumDonateComponent },
  { path: 'upgrade', component: MuseumUpgradeComponent },
  { path: 'giftShop', component: GiftShopComponent },
  { path: ':user', component: MuseumWingComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class MuseumRoutingModule { }
