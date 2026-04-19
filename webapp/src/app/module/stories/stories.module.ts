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
import { ListComponent } from './page/list/list.component';
import { StoriesRoutingModule } from "./stories-routing.module";
import { MarkdownModule } from "ngx-markdown";
import { ViewComponent } from './page/view/view.component';
import { DescribePetRangeComponent } from './components/describe-pet-range/describe-pet-range.component';
import { MissionTitlePipe } from './pipes/mission-title.pipe';
import { MissionPinComponent } from './components/mission-pin/mission-pin.component';
import { AssembleTeamDialog } from "./dialog/assemble-team/assemble-team.dialog";
import { MissionResultsDialog } from "./dialog/mission-results/mission-results.dialog";
import { HelpLinkComponent } from "../shared/component/help-link/help-link.component";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import { PaginatorComponent } from "../shared/component/paginator/paginator.component";
import { PetAppearanceComponent } from "../shared/component/pet-appearance/pet-appearance.component";
import { ResetRemixDialog } from "./dialog/reset-remix/reset-remix.dialog";

@NgModule({
  declarations: [
    ListComponent,
    ViewComponent,
    DescribePetRangeComponent,
    MissionTitlePipe,
    MissionPinComponent,
    AssembleTeamDialog,
    MissionResultsDialog,
    ResetRemixDialog,
  ],
  imports: [
    CommonModule,
    StoriesRoutingModule,
    MarkdownModule,
    HelpLinkComponent,
    LoadingThrobberComponent,
    PaginatorComponent,
    PetAppearanceComponent,
  ]
})
export class StoriesModule { }
