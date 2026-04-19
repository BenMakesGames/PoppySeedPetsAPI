/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
