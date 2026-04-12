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
