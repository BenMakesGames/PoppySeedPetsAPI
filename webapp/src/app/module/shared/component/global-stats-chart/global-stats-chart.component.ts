import { Component, ElementRef, Input, OnChanges, ViewChild } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import * as d3 from 'd3';
import { GlobalStatsTimeSeriesModel, GLOBAL_STATS_METRICS, GLOBAL_STATS_PERIODS } from '../../../../model/global-stats.model';

@Component({
    selector: 'app-global-stats-chart',
    imports: [CommonModule, FormsModule],
    template: `
    <div class="controls">
      <div class="select-container">
        <div>Metric:</div>
        <select [(ngModel)]="selectedMetric" (change)="updateChart()">
          @for(metric of metrics; track metric.value)
          {
            <option [ngValue]="metric">{{ metric.label }}</option>
          }
        </select>
      </div>
      <div class="select-container">
        <div>Period:</div>
        <select [(ngModel)]="selectedPeriod" (change)="updateChart()">
          @for(period of periods; track period.value)
          {
            <option [ngValue]="period">{{ period.label }}</option>
          }
        </select>
      </div>
    </div>
    <div class="chart-container">
      <svg #chart [style.width.px]="CHART_WIDTH" [style.height.px]="CHART_HEIGHT"></svg>
    </div>
  `,
    styleUrls: ['./global-stats-chart.component.scss']
})
export class GlobalStatsChartComponent implements OnChanges {
  @Input() data: GlobalStatsTimeSeriesModel[];

  @ViewChild('chart', { static: true }) private chartContainer: ElementRef;
  readonly CHART_WIDTH = 700;
  readonly CHART_HEIGHT = 300;
  readonly CHART_MARGIN = { top: 10, right: 5, bottom: 60, left: 70 };

  selectedMetric = GLOBAL_STATS_METRICS[0];
  selectedPeriod = GLOBAL_STATS_PERIODS[0];
  metrics = GLOBAL_STATS_METRICS;
  periods = GLOBAL_STATS_PERIODS;

  private svg: any;

  ngOnChanges() {
    if (this.data) {
      this.initializeChart();
      this.updateChart();
    }
  }

  private initializeChart() {
    this.svg = d3.select(this.chartContainer.nativeElement);
    
    // Clear any existing content
    this.svg.selectAll('*').remove();
    
    // Set the viewBox for responsive scaling
    this.svg.attr('viewBox', `0 0 ${this.CHART_WIDTH} ${this.CHART_HEIGHT}`);
  }

  updateChart() {
    if (!this.data || !this.selectedMetric || !this.selectedPeriod) return;

    const width = this.CHART_WIDTH - this.CHART_MARGIN.left - this.CHART_MARGIN.right;
    const height = this.CHART_HEIGHT - this.CHART_MARGIN.top - this.CHART_MARGIN.bottom;

    // Clear previous content
    this.svg.selectAll('*').remove();

    const g = this.svg.append('g')
      .attr('transform', `translate(${this.CHART_MARGIN.left},${this.CHART_MARGIN.top})`);

    // Prepare data
    const data = this.data
      .map(d => ({
        date: new Date(d.date),
        value: d[`${this.selectedMetric.value}${this.selectedPeriod.value}`]
      }))
      .sort((a, b) => a.date.getTime() - b.date.getTime());

    // Create scales
    const x = d3.scaleTime()
      .domain(d3.extent(data, d => d.date) as [Date, Date])
      .range([0, width]);

    const y = d3.scaleLinear()
      .domain([0, d3.max(data, d => d.value) as number])
      .nice()
      .range([height, 0]);

    // Add X axis
    g.append('g')
      .attr('transform', `translate(0,${height})`)
      .call(d3.axisBottom(x)
        .tickFormat(d3.timeFormat('%b %e')))
      .selectAll('text')
      .style('text-anchor', 'end')
      .attr('dx', '-.8em')
      .attr('dy', '.15em')
      .attr('transform', 'rotate(-45)');

    // Add Y axis
    g.append('g')
      .call(d3.axisLeft(y)
        .tickFormat(d3.format('d')));

    // Add the line
    const line = d3.line<any>()
      .x(d => x(d.date))
      .y(d => y(d.value));

    g.append('path')
      .datum(data)
      .attr('fill', 'none')
      .attr('stroke', 'rgb(var(--color-text-on-content-background))')
      .attr('stroke-width', 1.25)
      .attr('d', line);

    // Add dots
    g.selectAll('circle')
      .data(data)
      .enter()
      .append('circle')
      .attr('cx', d => x(d.date))
      .attr('cy', d => y(d.value))
      .attr('r', 4)
      .attr('fill', 'rgb(var(--color-text-on-content-background))');
  }
} 