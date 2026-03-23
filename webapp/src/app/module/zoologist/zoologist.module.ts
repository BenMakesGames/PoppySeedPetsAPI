import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ZoologistComponent } from './page/zoologist/zoologist.component';
import { MarkdownModule } from "ngx-markdown";
import { ZoologistRoutingModule } from "./zoologist-routing.module";
import { FormsModule } from "@angular/forms";
import { ShowSpeciesComponent } from './page/show-species/show-species.component';
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import { UrlPaginatorComponent } from "../shared/component/url-paginator/url-paginator.component";
import { NpcDialogComponent } from "../shared/component/npc-dialog/npc-dialog.component";
import { DateAndTimeComponent } from "../shared/component/date-and-time/date-and-time.component";
import { PetAppearanceComponent } from "../shared/component/pet-appearance/pet-appearance.component";
import { PaginatorComponent } from "../shared/component/paginator/paginator.component";


@NgModule({
  declarations: [
    ZoologistComponent,
    ShowSpeciesComponent
  ],
  imports: [
    CommonModule,
    MarkdownModule,
    ZoologistRoutingModule,
    MarkdownModule.forChild(),
    FormsModule,
    LoadingThrobberComponent,
    UrlPaginatorComponent,
    NpcDialogComponent,
    DateAndTimeComponent,
    PetAppearanceComponent,
    PaginatorComponent,
  ]
})
export class ZoologistModule { }
