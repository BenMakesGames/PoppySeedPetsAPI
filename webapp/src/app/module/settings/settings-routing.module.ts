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
