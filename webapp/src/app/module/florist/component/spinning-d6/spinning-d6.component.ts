/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, Input, OnChanges, OnDestroy } from '@angular/core';

@Component({
    selector: 'app-spinning-d6',
    templateUrl: './spinning-d6.component.html',
    styleUrls: ['./spinning-d6.component.scss'],
    standalone: false
})
export class SpinningD6Component implements OnChanges, OnDestroy {

  @Input() result: number|string|null = null;
  @Input() size: string = '1rem';

  roll: number|string;
  rollInterval: number|undefined;

  ngOnChanges(): void {
    if(this.result === null)
    {
      this.rollDie();
      this.rollInterval = window.setInterval(() => { this.rollDie(); }, 100);
    }
    else
    {
      if(this.rollInterval)
      {
        clearInterval(this.rollInterval);
        delete this.rollInterval;
      }
    }
  }

  ngOnDestroy(): void {
    if(this.rollInterval)
      clearInterval(this.rollInterval);
  }

  private rollDie()
  {
    let possibilities = [ 1, 1, 1, 2, 2, 2, 3, 3, 3, 4, 4, 4, 5, 5, 5, 6, 6, 6, this.dayOfTheWeek() ];

    possibilities = possibilities.filter(p => p != this.roll);

    this.roll = possibilities[Math.floor(Math.random() * possibilities.length)];
  }

  private dayOfTheWeek()
  {
    let day = (new Date()).getDay();
    return [ 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday' ][day];
  }
}
