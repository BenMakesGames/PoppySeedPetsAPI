import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import {BookstoreRoutingModule} from "./bookstore-routing.module";
import {BookstoreComponent} from "./page/bookstore/bookstore.component";
import {MarkdownModule} from "ngx-markdown";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import { NpcDialogComponent } from "../shared/component/npc-dialog/npc-dialog.component";
import { MoneysComponent } from "../shared/component/moneys/moneys.component";



@NgModule({
  declarations: [
    BookstoreComponent
  ],
  imports: [
    CommonModule,
    BookstoreRoutingModule,
    MarkdownModule,
    LoadingThrobberComponent,
    NpcDialogComponent,
    MoneysComponent
  ]
})
export class BookstoreModule { }
