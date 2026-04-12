/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  standalone: true,
  name: 'describeAge'
})
export class DescribeAgePipe implements PipeTransform {

  static YEAR = 365 * 24 * 60 * 60;
  static MONTH = 365 * 24 * 60 * 60 / 12;
  static WEEK = 7 * 24 * 60 * 60;
  static DAY = 24 * 60 * 60;
  static HOUR = 60 * 60;
  static MINUTE = 60;

  transform(value: number, numParts: number = 2): string {
    let parts = [];

    if(value >= DescribeAgePipe.YEAR)
    {
      const years = Math.floor(value / DescribeAgePipe.YEAR);
      parts.push(years + ' ' + (years !== 1 ? 'years' : 'year'));
      value -= years * DescribeAgePipe.YEAR;
    }

    if(value >= DescribeAgePipe.MONTH)
    {
      const months = Math.floor(value / DescribeAgePipe.MONTH);
      parts.push(months + ' ' + (months !== 1 ? 'months' : 'month'));
      value -= months * DescribeAgePipe.MONTH;
    }

    if(value >= DescribeAgePipe.WEEK)
    {
      const weeks = Math.floor(value / DescribeAgePipe.WEEK);
      parts.push(weeks + ' ' + (weeks !== 1 ? 'weeks' : 'week'));
      value -= weeks * DescribeAgePipe.WEEK;
    }

    if(value >= DescribeAgePipe.DAY)
    {
      const days = Math.floor(value / DescribeAgePipe.DAY);
      parts.push(days + ' ' + (days !== 1 ? 'days' : 'day'));
      value -= days * DescribeAgePipe.DAY;
    }

    if(value >= DescribeAgePipe.HOUR)
    {
      const hours = Math.floor(value / DescribeAgePipe.HOUR);
      parts.push(hours + ' ' + (hours !== 1 ? 'hours' : 'hour'));
      value -= hours * DescribeAgePipe.HOUR;
    }

    if(value >= DescribeAgePipe.MINUTE)
    {
      const minutes = Math.floor(value / DescribeAgePipe.MINUTE);
      parts.push(minutes + ' ' + (minutes !== 1 ? 'minutes' : 'minute'));
      value -= minutes * DescribeAgePipe.MINUTE;
    }

    if(parts.length === 0)
      return 'a very short time';
    else
      return parts.slice(0, numParts).join(', ');
  }

}
