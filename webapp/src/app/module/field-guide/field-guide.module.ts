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
import { FieldGuideComponent } from './page/field-guide/field-guide.component';
import { FieldGuideRoutingModule } from "./field-guide-routing.module";
import { MarkdownModule } from "ngx-markdown";
import { DateOnlyComponent } from "../shared/component/date-only/date-only.component";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";


@NgModule({
  declarations: [
    FieldGuideComponent,
  ],
  imports: [
    CommonModule,
    FieldGuideRoutingModule,
    MarkdownModule,
    DateOnlyComponent,
    LoadingThrobberComponent,
  ]
})
export class FieldGuideModule { }
