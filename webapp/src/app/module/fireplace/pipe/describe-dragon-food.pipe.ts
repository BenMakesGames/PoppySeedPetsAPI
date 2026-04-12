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
    name: 'describeDragonFood',
    standalone: false
})
export class DescribeDragonFoodPipe implements PipeTransform {

  transform(value: string[]): string {
    let spicy = false;
    let meaty = false;
    let fishy = false;

    value.forEach(v => {
      if(v.indexOf('spicy') >= 0) spicy = true;
      if(v.indexOf('meaty') >= 0) meaty = true;
      if(v.indexOf('fishy') >= 0) fishy = true;
    });

    let list = [];

    if(spicy) list.push('spicy');
    if(meaty) list.push('meaty');
    if(fishy) list.push('fishy');

    return list.join(', ');
  }

}
