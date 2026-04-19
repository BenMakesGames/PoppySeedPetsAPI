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
import {MustBeLoggedInGuard} from "../../guard/must-be-logged-in.guard";
import {AppearanceComponent} from "./page/settings/appearance/appearance.component";
import {AccountComponent} from "./page/settings/account/account.component";
import {FollowingComponent} from "./page/settings/following/following.component";
import {BehaviorComponent} from "./page/settings/behavior/behavior.component";
import { FollowersComponent } from "./page/settings/followers/followers.component";
import { OnTheWebPageComponent } from "./page/settings/on-the-web-page/on-the-web-page.component";
import { PatronRewardsComponent } from "./page/settings/patron-rewards/patron-rewards.component";
import { SoundsComponent } from "./page/settings/sounds/sounds.component";

const routes: Routes = [
  { path: 'visuals', component: AppearanceComponent, canActivate: [ MustBeLoggedInGuard ] },
  { path: 'audio', component: SoundsComponent, canActivate: [ MustBeLoggedInGuard ] },
  { path: 'behavior', component: BehaviorComponent, canActivate: [ MustBeLoggedInGuard ] },
  { path: 'myAccount', component: AccountComponent, canActivate: [ MustBeLoggedInGuard ] },
  { path: 'following', component: FollowingComponent, canActivate: [ MustBeLoggedInGuard ] },
  { path: 'followers', component: FollowersComponent, canActivate: [ MustBeLoggedInGuard ] },
  { path: 'myProfile', component: OnTheWebPageComponent, canActivate: [ MustBeLoggedInGuard ] },
  { path: 'patreon', component: PatronRewardsComponent, canActivate: [ MustBeLoggedInGuard ] },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class SettingsRoutingModule { }
