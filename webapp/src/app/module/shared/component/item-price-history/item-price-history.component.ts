/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {
  Component,
  ElementRef,
  Input,
  OnChanges,
  OnDestroy,
  ViewChild
} from '@angular/core';
import * as d3 from "d3";
import {Subscription} from "rxjs";
import { CommonModule } from "@angular/common";
import { MoneysComponent } from "../moneys/moneys.component";

@Component({
    selector: 'app-item-price-history',
    templateUrl: './item-price-history.component.html',
    imports: [
        CommonModule,
        MoneysComponent,
    ],
    styleUrls: ['./item-price-history.component.scss']
})
export class ItemPriceHistoryComponent implements OnChanges, OnDestroy {

  @Input() data: MarketHistoryResponseData;

  @ViewChild('chart', { 'static': true }) private chartContainer: ElementRef;

  private svg: any;

  viewing = null;
  marketLastHistory: MarketItemHistoryData = null;
  marketHistory: MarketItemHistoryData[];
  sixDaysAgo: Date;

  smallestPrice: number;
  largestPrice: number;
  prices: number[] = [];

  marketHistoryAjax: Subscription;

  static readonly CHART_WIDTH = 700;
  static readonly CHART_HEIGHT = 200;
  static readonly CHART_VERTICAL_PADDING = 25;
  static readonly CHART_HORIZONTAL_PADDING = 25;
  static readonly CHART_DAYS = 7;
  static readonly CHART_CIRCLE_DIAMETER_PERCENT = 0.25;

  doToggleDisplay()
  {
    if(this.marketHistory.length >= 2)
      this.viewing = this.viewing === 'chart' ? 'table' : 'chart';
  }

  ngOnDestroy(): void {
    if(this.marketHistoryAjax)
      this.marketHistoryAjax.unsubscribe();
  }

  ngOnChanges()
  {
    this.sixDaysAgo = new Date();
    this.sixDaysAgo.setUTCDate(this.sixDaysAgo.getUTCDate() - 7);

    const now = Date.now();
    const timeParser = d3.timeParse('%Y-%m-%d');

    this.marketLastHistory = this.data.lastHistory;

    this.marketHistory = this.data.history
      .map(d => {
        return {
          ... d,
          daysAgo: Math.floor((now - timeParser(d.date).getTime()) / (1000 * 60 * 60 * 24))
        };
      })
      .sort((a, b) => a.daysAgo < b.daysAgo ? -1 : 1)
    ;

    this.smallestPrice = Math.min(...this.marketHistory.map(d => d.minPrice));
    this.largestPrice = Math.max(...this.marketHistory.map(d => d.maxPrice));

    const range = (this.largestPrice - this.smallestPrice);

    this.prices = [];

    for(let i = 0; i <= 3; i++)
      this.prices.push(this.largestPrice - (i * range / 3));

    if(this.marketHistory.length >= 2)
    {
      this.buildSvg();
      this.viewing = 'chart';
    }
    else
      this.viewing = 'table';
  }

  private buildSvg()
  {
    const data = this.marketHistory;

    this.svg = d3.select(this.chartContainer.nativeElement);
    this.svg.select('g').remove();

    let g = this.svg
      .append('g')
      .attr('transform', 'translate(0, ' + ItemPriceHistoryComponent.CHART_VERTICAL_PADDING + ')')
    ;

    const x = d3.scaleLinear()
      .domain([ ItemPriceHistoryComponent.CHART_DAYS, 1 ])
      .range([ ItemPriceHistoryComponent.CHART_HORIZONTAL_PADDING, ItemPriceHistoryComponent.CHART_WIDTH - ItemPriceHistoryComponent.CHART_HORIZONTAL_PADDING * 2 ])
    ;

    const pointDiameter = (ItemPriceHistoryComponent.CHART_WIDTH / ItemPriceHistoryComponent.CHART_DAYS) * ItemPriceHistoryComponent.CHART_CIRCLE_DIAMETER_PERCENT;

    const chartAreaHeight = ItemPriceHistoryComponent.CHART_HEIGHT - ItemPriceHistoryComponent.CHART_VERTICAL_PADDING * 2;

    const y = d3.scaleLinear()
        .domain([ this.smallestPrice, this.largestPrice ])
        .range([ chartAreaHeight, 0 ])
    ;

    const range = (this.largestPrice - this.smallestPrice);

    for(let i = 0; i < 4; i++)
    {
      const lineY = y(this.largestPrice - (i * range / 3));

      g.append('line')
        .attr('stroke', 'rgb(var(--color-text-on-content-background))')
        .attr('stroke-width', 3)
        .attr('opacity', 0.2)
        .attr('x1', 0)
        .attr('y1', lineY)
        .attr('x2', ItemPriceHistoryComponent.CHART_WIDTH)
        .attr('y2', lineY)
      ;
    }

    g.selectAll('rect')
      .data(data)
      .enter()
      .append('rect')
      .attr('fill', 'rgb(var(--color-primary))')
      .attr('stroke', 'none')
      .attr('opacity', '0.5')
      .attr("x", function(d) { return x(d.daysAgo) - 2; })
      .attr("y", d => y(d.maxPrice))
      .attr("width", 4)
      .attr('height', d => y(d.minPrice) - y(d.maxPrice))
    ;

    g.selectAll('circles')
      .data(data)
      .enter()
      .append('circle')
      .attr('fill', 'rgb(var(--color-primary))')
      .attr('stroke', 'none')
      .attr("cx", function(d) { return x(d.daysAgo); })
      .attr("cy", function(d) { return y(d.averagePrice); })
      .attr("r", pointDiameter / 2)
    ;
  }
}

export interface MarketHistoryResponseData {
  history: MarketItemHistoryData[];
  lastHistory: MarketItemHistoryData;
}

export interface MarketItemHistoryData {
  daysAgo: number;
  date: string;
  minPrice: number;
  maxPrice: number;
  averagePrice: number;
}
