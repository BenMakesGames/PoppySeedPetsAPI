import {Component, OnDestroy, OnInit} from '@angular/core';
import {ApiService} from "../../module/shared/service/api.service";
import {MyStatsSerializationGroup} from "../../model/my-stats.serialization-group";
import {Subscription} from "rxjs";
import { LoadingThrobberComponent } from "../../module/shared/component/loading-throbber/loading-throbber.component";
import { RouterLink } from "@angular/router";
import { FormsModule } from "@angular/forms";
import { DateAndTimeComponent } from "../../module/shared/component/date-and-time/date-and-time.component";
import { CommonModule } from "@angular/common";
import { PreservePreviousSong } from "../../app.component";
import { GlobalStatsTimeSeriesModel } from "../../model/global-stats.model";
import { GlobalStatsChartComponent } from "../../module/shared/component/global-stats-chart/global-stats-chart.component";

@Component({
    templateUrl: './stats.component.html',
    imports: [
        LoadingThrobberComponent,
        RouterLink,
        FormsModule,
        DateAndTimeComponent,
        CommonModule,
        GlobalStatsChartComponent
    ],
    styleUrls: ['./stats.component.scss']
})
export class StatsComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Stats', song: PreservePreviousSong };

  active = 'private';

  myStats: MyStatsSerializationGroup[];
  myStatsAjax = Subscription.EMPTY;
  sort = 'lastTime';

  globalStats: GlobalStatsTimeSeriesModel[];
  globalStatsAjax = Subscription.EMPTY;

  constructor(private api: ApiService) { }

  ngOnInit() {
    this.myStatsAjax = this.api.get<MyStatsSerializationGroup[]>('/account/stats').subscribe(d => {
      this.myStats = d.data;
      this.doSortBy();
    });

    this.globalStatsAjax = this.api.get<GlobalStatsTimeSeriesModel[]>('/globalStats/today').subscribe(d => {
      this.globalStats = d.data;
    });
  }

  ngOnDestroy(): void {
    this.myStatsAjax.unsubscribe();
    this.globalStatsAjax.unsubscribe();
  }

  doSortBy() {
    if(this.sort === 'lastTime') {
      this.myStats = this.myStats.sort((a, b) => {
        return a.lastTime > b.lastTime ? -1 : 1;
      })
    }
    else if(this.sort === 'firstTime') {
      this.myStats = this.myStats.sort((a, b) => {
        return a.firstTime > b.firstTime ? -1 : 1;
      })
    }
    else if(this.sort === 'stat') {
      this.myStats = this.myStats.sort((a, b) => {
        return a.stat > b.stat ? 1 : -1;
      })
    }
    else if(this.sort === 'value') {
      this.myStats = this.myStats.sort((a, b) => {
        return a.value > b.value ? -1 : 1;
      })
    }
  }
}
