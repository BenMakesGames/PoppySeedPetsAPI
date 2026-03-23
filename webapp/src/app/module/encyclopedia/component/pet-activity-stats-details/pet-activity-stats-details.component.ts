import { Component, input, OnInit } from '@angular/core';
import { DonutChartDataPointModel } from "../../../../model/charts/donut-chart-data-point.model";
import { ChartModel } from "../../../../model/charts/chart.model";

@Component({
    selector: 'app-pet-activity-stats-details',
    templateUrl: './pet-activity-stats-details.component.html',
    styleUrls: ['./pet-activity-stats-details.component.scss'],
    standalone: false
})
export class PetActivityStatsDetailsComponent implements OnInit {

  charts = input.required<ChartModel<DonutChartDataPointModel>[]>();
  activeChart: ChartModel<DonutChartDataPointModel>;
  activeChartData: DonutChartDataPointModel[];

  ngOnInit() {
    this.doSelectChart(0);
  }

  doSelectChart(i: number)
  {
    this.activeChart = this.charts()[i];
    this.activeChartData = this.activeChart.data.filter(a => a.value > 0).sort((a, b) => b.value - a.value);
  }
}
