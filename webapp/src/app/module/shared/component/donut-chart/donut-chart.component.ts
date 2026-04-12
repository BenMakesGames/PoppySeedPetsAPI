/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, ElementRef, Input, OnChanges, ViewChild} from '@angular/core';

import * as d3 from 'd3';
import {DonutChartDataPointModel} from "../../../../model/charts/donut-chart-data-point.model";

@Component({
  standalone: true,
  selector: 'app-donut-chart',
  template: '<svg viewBox="0 0 100 100" #chart></svg>',
  styleUrls: ['./donut-chart.component.scss']
})
export class DonutChartComponent implements OnChanges {

  @Input() data: DonutChartDataPointModel[];
  @Input() showTopX: number = 0;

  @ViewChild('chart', { 'static': true }) private chartContainer: ElementRef;

  private svg: any;

  constructor() { }

  ngOnChanges() {
    this.buildSvg();
  }

  private buildSvg()
  {
    this.svg = d3.select(this.chartContainer.nativeElement);
    this.svg.select('g').remove();

    let g = this.svg
      .append('g')
      .attr('transform', 'translate(50, 50)')
    ;

    let pie = d3.pie<DonutChartDataPointModel>().value((d: DonutChartDataPointModel) => d.value);
    let dataReady = pie(this.data);

    g.selectAll('.slice')
      .data(dataReady)
      .enter()
      .append('path')
      .attr('d', d => {
        const outerRadius = d.data.percentDeleted ? 50 - d.data.percentDeleted * 20 : 50;
        return d3.arc().innerRadius(30).outerRadius(outerRadius)(d)
      })
      .attr('fill', d => d.data.color)
      .attr('stroke-width', 0)
    ;

    if(this.data.some(d => d.percentDeleted && d.percentDeleted > 0))
    {
      g.selectAll('.slice-failures')
        .data(dataReady)
        .enter()
        .append('path')
        .attr('d', d => {
          const innerRadius = 50 - d.data.percentDeleted * 20;
          return d3.arc().innerRadius(innerRadius).outerRadius(50)(d);
        })
        .attr('fill', d => d.data.color + '88')
        .attr('stroke-width', 0)
      ;
    }

    if(this.showTopX > 0)
    {
      let topX = this.data.sort((a, b) => b.value - a.value).slice(0, this.showTopX);

      g.selectAll('.label')
        .data(topX)
        .enter()
        .append('text')
        .attr('text-anchor', 'middle')
        .attr('fill', 'rgb(var(--color-text-on-content-background))')
        .attr('dy', (d, i) => (i - this.showTopX / 2 + 0.75) + 'em')
        .attr('style', 'font-size: 0.6667rem')
        .text((d, i) => Math.round(d.value * 100) + '% ' + d.label)
      ;
    }
  }
}
