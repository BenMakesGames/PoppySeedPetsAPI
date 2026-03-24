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
