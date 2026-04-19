/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
