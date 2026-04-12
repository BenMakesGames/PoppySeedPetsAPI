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
import { SurveyRoutingModule } from "./survey-routing.module";
import { SurveyComponent } from './page/survey/survey.component';
import { FormsModule } from "@angular/forms";
import { MarkdownModule } from "ngx-markdown";
import {FormFieldComponent} from "../shared/component/form-field/form-field.component";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import {PspTimeComponent} from "../shared/component/psp-time/psp-time.component";


@NgModule({
  declarations: [
    SurveyComponent
  ],
    imports: [
        CommonModule,
        SurveyRoutingModule,
        FormsModule,
        MarkdownModule,
        FormFieldComponent,
        LoadingThrobberComponent,
        PspTimeComponent
    ]
})
export class SurveyModule { }
