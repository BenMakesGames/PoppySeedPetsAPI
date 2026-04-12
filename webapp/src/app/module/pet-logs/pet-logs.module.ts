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
import { PetLogsComponent } from './page/pet-logs/pet-logs.component';
import {PetLogsRoutingModule} from "./pet-logs-routing.module";
import {MarkdownModule} from "ngx-markdown";
import { ActivityTagInputComponent } from "./component/activity-tag-input/activity-tag-input.component";
import { FormsModule } from "@angular/forms";
import { PlayerLogsComponent } from "./page/player-logs/player-logs.component";
import { JournalNavComponent } from "./component/journal-nav/journal-nav.component";
import { UserActivityTagInputComponent } from "./component/user-activity-tag-input/user-activity-tag-input.component";
import { UrlPaginatorComponent } from "../shared/component/url-paginator/url-paginator.component";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import { PetActivityLogTagComponent } from "../shared/component/pet-activity-log-tag/pet-activity-log-tag.component";
import { PetChangesComponent } from "../shared/component/pet-changes/pet-changes.component";
import { PetAppearanceComponent } from "../shared/component/pet-appearance/pet-appearance.component";
import {HasRolePipe} from "../shared/pipe/has-role.pipe";
import { ContainsLocationTagPipe } from "./pipes/contains-location-tag.pipe";
import {EmojiOrFaComponent} from "../shared/component/emoji-or-fa/emoji-or-fa.component";



@NgModule({
  declarations: [
    PetLogsComponent,
    PlayerLogsComponent,
    ActivityTagInputComponent,
    UserActivityTagInputComponent,
    JournalNavComponent,
  ],
    imports: [
        CommonModule,
        PetLogsRoutingModule,
        MarkdownModule,
        FormsModule,
        UrlPaginatorComponent,
        LoadingThrobberComponent,
        PetActivityLogTagComponent,
        PetChangesComponent,
        PetAppearanceComponent,
        HasRolePipe,
        ContainsLocationTagPipe,
        EmojiOrFaComponent,
    ]
})
export class PetLogsModule { }
