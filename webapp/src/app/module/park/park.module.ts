import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import {ParkComponent} from "./page/park/park.component";
import {HistoryComponent} from "./page/history/history.component";
import {ParkEventDetailsDialog} from "./dialog/park-event-details/park-event-details.dialog";
import {RouterModule} from "@angular/router";
import {MarkdownModule} from "ngx-markdown";
import {FormsModule} from "@angular/forms";
import {ParkRoutingModule} from "./park-routing.module";
import { UrlPaginatorComponent } from "../shared/component/url-paginator/url-paginator.component";
import { PetAppearanceComponent } from "../shared/component/pet-appearance/pet-appearance.component";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import { DateAndTimeComponent } from "../shared/component/date-and-time/date-and-time.component";
import { SluggifyPipe } from "../shared/pipe/sluggify.pipe";



@NgModule({
  declarations: [
    ParkComponent,
    HistoryComponent,
    ParkEventDetailsDialog,
  ],
  imports: [
    CommonModule,
    RouterModule,
    MarkdownModule,
    FormsModule,
    ParkRoutingModule,
    UrlPaginatorComponent,
    PetAppearanceComponent,
    LoadingThrobberComponent,
    DateAndTimeComponent,
    SluggifyPipe
  ]
})
export class ParkModule { }
