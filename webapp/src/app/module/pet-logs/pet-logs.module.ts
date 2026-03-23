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
