import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { NewsComponent } from "./page/news/news.component";
import { UpsertArticleDialog } from "./dialog/upsert-article/upsert-article.dialog";
import { NewsRoutingModule } from "./news-routing.module";
import { MarkdownModule } from "ngx-markdown";
import { FormsModule } from "@angular/forms";
import { ListDesignGoalsComponent } from "../shared/component/list-design-goals/list-design-goals.component";
import { HasRolePipe } from "../shared/pipe/has-role.pipe";
import { PaginatorComponent } from "../shared/component/paginator/paginator.component";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import { InArrayPipe } from "../shared/pipe/in-array.pipe";
import { FormFieldComponent } from "../shared/component/form-field/form-field.component";
import {PspTimeComponent} from "../shared/component/psp-time/psp-time.component";


@NgModule({
  declarations: [
    NewsComponent,
    UpsertArticleDialog,
  ],
    imports: [
        CommonModule,
        NewsRoutingModule,
        MarkdownModule,
        FormsModule,
        ListDesignGoalsComponent,
        HasRolePipe,
        PaginatorComponent,
        LoadingThrobberComponent,
        InArrayPipe,
        FormFieldComponent,
        PspTimeComponent
    ]
})
export class NewsModule { }
