import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import {PlazaRoutingModule} from "./plaza-routing.module";
import {PlazaComponent} from "./page/plaza/plaza.component";
import { WeatherForecastComponent } from './component/weather-forecast/weather-forecast.component';
import { MarkdownModule } from "ngx-markdown";
import { ExplainHolidayComponent } from './component/explain-holiday/explain-holiday.component';
import { NpcDialogComponent } from "../shared/component/npc-dialog/npc-dialog.component";
import { LoadingThrobberComponent } from "../shared/component/loading-throbber/loading-throbber.component";
import { HasUnlockedFeaturePipe } from "../shared/pipe/has-unlocked-feature.pipe";
import { RecyclingPointsComponent } from "../shared/component/recycling-points/recycling-points.component";
import { HelpLinkComponent } from "../shared/component/help-link/help-link.component";
import { SluggifyPipe } from "../shared/pipe/sluggify.pipe";
import { CurrentWeatherComponent } from "../shared/component/current-weather/current-weather.component";
import { EventCalendarComponent } from "./page/event-calendar/event-calendar.component";
import { DescribeCalendarDayDialog } from "./dialog/describe-calendar-day/describe-calendar-day.dialog";
import { MilestoneProgressComponent } from "../shared/component/milestone-progress/milestone-progress.component";
import { FeedMonsterDialog } from "./dialog/feed-monster/feed-monster.dialog";
import {FireplaceModule} from "../fireplace/fireplace.module";
import {InventoryItemComponent} from "../shared/component/inventory-item/inventory-item.component";
import { ClaimRewardsDialog } from "./dialog/claim-rewards/claim-rewards.dialog";
import { DateOnlyComponent } from "../shared/component/date-only/date-only.component";
import { MilestoneValuesPipe } from "./pipes/milestone-values.pipe";
import { MonsterProgressComponent } from "./component/monster-progress/monster-progress.component";
import { HelloDialogComponent } from "./component/hello-dialog/hello-dialog.component";
import { TheSpiritNamePipe } from "./pipes/the-spirit-name.pipe";
import { SpiritNamePipe } from "./pipes/spirit-name.pipe";
import { SpiritGraphicPipe } from "./pipes/spirit-graphic.pipe";


@NgModule({
  declarations: [
    PlazaComponent,
    WeatherForecastComponent,
    ExplainHolidayComponent,
    EventCalendarComponent,
    DescribeCalendarDayDialog,
    FeedMonsterDialog,
    ClaimRewardsDialog,
    MilestoneValuesPipe,
    HelloDialogComponent,
  ],
  imports: [
    CommonModule,
    PlazaRoutingModule,
    MarkdownModule,
    NpcDialogComponent,
    LoadingThrobberComponent,
    HasUnlockedFeaturePipe,
    RecyclingPointsComponent,
    HelpLinkComponent,
    SluggifyPipe,
    CurrentWeatherComponent,
    MilestoneProgressComponent,
    FireplaceModule,
    InventoryItemComponent,
    DateOnlyComponent,
    MonsterProgressComponent,
    TheSpiritNamePipe,
    SpiritNamePipe,
    SpiritGraphicPipe
  ]
})
export class PlazaModule { }
