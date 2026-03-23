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
