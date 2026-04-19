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
import { BadgesRoutingModule } from "./badges-routing.module";
import { BadgesComponent } from './page/badges/badges.component';
import { BadgeProgressComponent } from './components/badge-progress/badge-progress.component';
import { BadgeTitlePipe } from './pipes/badge-title.pipe';
import { BadgeDescriptionPipe } from './pipes/badge-description.pipe';
import { TraderModule } from "../trader/trader.module";
import { BadgeBackgroundPipe } from './pipes/badge-background-color.pipe';
import { BadgeImagePipe } from './pipes/badge-image.pipe';
import { ClaimedComponent } from "./page/claimed/claimed.component";
import { MarkdownModule } from "ngx-markdown";
import { ShowcaseComponent } from "./page/showcase/showcase.component";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import { UrlPaginatorComponent } from "../shared/component/url-paginator/url-paginator.component";
import { DateAndTimeComponent } from "../shared/component/date-and-time/date-and-time.component";
import { PlayerNameComponent } from "../shared/component/player-name/player-name.component";
import { PetBadgesComponent } from "./page/pet-badges/pet-badges.component";
import { PetBadgeImgSrcPipe } from "../shared/pipe/pet-badge-img-src.pipe";
import { PetBadgeNamePipe } from "../shared/pipe/pet-badge-name.pipe";
import { DateOnlyComponent } from "../shared/component/date-only/date-only.component";
import { FormsModule, ReactiveFormsModule } from "@angular/forms";

@NgModule({
  declarations: [
    BadgesComponent,
    BadgeProgressComponent,
    BadgeTitlePipe,
    BadgeDescriptionPipe,
    BadgeBackgroundPipe,
    BadgeImagePipe,
    ClaimedComponent,
    ShowcaseComponent,
    PetBadgesComponent
  ],
  imports: [
    CommonModule,
    BadgesRoutingModule,
    TraderModule,
    MarkdownModule,
    LoadingThrobberComponent,
    UrlPaginatorComponent,
    DateAndTimeComponent,
    PlayerNameComponent,
    PetBadgeImgSrcPipe,
    PetBadgeNamePipe,
    DateOnlyComponent,
    ReactiveFormsModule,
    FormsModule,
  ]
})
export class BadgesModule { }
