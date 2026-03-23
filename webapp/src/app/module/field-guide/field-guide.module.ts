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
