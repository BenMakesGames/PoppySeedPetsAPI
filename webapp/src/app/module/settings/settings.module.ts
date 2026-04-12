/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import {AccountComponent} from "./page/settings/account/account.component";
import {AppearanceComponent} from "./page/settings/appearance/appearance.component";
import {SettingsPanelComponent} from "./component/settings-panel/settings-panel.component";
import {SettingsNavComponent} from "./component/settings-nav/settings-nav.component";
import {FormsModule} from "@angular/forms";
import {SettingsRoutingModule} from "./settings-routing.module";
import { FollowingComponent } from './page/settings/following/following.component';
import {BehaviorComponent} from "./page/settings/behavior/behavior.component";
import { FollowersComponent } from "./page/settings/followers/followers.component";
import { FollowersListComponent } from "./component/followers-list/followers-list.component";
import { FollowingListComponent } from "./component/following-list/following-list.component";
import { OnTheWebComponent } from "./component/on-the-web/on-the-web.component";
import { OnTheWebPageComponent } from "./page/settings/on-the-web-page/on-the-web-page.component";
import { EncyclopediaModule } from "../encyclopedia/encyclopedia.module";
import { AddLinkDialog } from "./dialog/add-link/add-link.dialog";
import { MatDialogModule } from "@angular/material/dialog";
import { PatronRewardsComponent } from "./page/settings/patron-rewards/patron-rewards.component";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import { PaginatorComponent } from "../shared/component/paginator/paginator.component";
import { PlayerNameComponent } from "../shared/component/player-name/player-name.component";
import { DateAndTimeComponent } from "../shared/component/date-and-time/date-and-time.component";
import { SoundsComponent } from "./page/settings/sounds/sounds.component";

@NgModule({
  declarations: [
    AccountComponent,
    AppearanceComponent,
    SettingsPanelComponent,
    SettingsNavComponent,
    FollowingComponent,
    BehaviorComponent,
    FollowersComponent,
    FollowersListComponent,
    FollowingListComponent,
    OnTheWebComponent,
    OnTheWebPageComponent,
    AddLinkDialog,
    PatronRewardsComponent,
    SoundsComponent,
  ],
  imports: [
    CommonModule,
    SettingsRoutingModule,
    FormsModule,
    EncyclopediaModule,
    MatDialogModule,
    LoadingThrobberComponent,
    PaginatorComponent,
    PlayerNameComponent,
    DateAndTimeComponent,
  ],
})
export class SettingsModule { }
