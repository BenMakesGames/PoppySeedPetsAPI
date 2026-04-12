/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, Input, OnDestroy, OnInit } from '@angular/core';

@Component({
    selector: 'app-minute-timer',
    imports: [],
    templateUrl: './minute-timer.component.html',
    styleUrl: './minute-timer.component.scss'
})
export class MinuteTimerComponent implements OnInit, OnDestroy {
  @Input({ required: true }) minutesRemaining: number;

  seconds = 60;

  interval: number|undefined;

  ngOnInit() {
    if(this.minutesRemaining <= 0)
      this.seconds = 0;

    this.interval = window.setInterval(() => {
      if(this.minutesRemaining <= 0)
      {
        this.seconds = 0;
        return;
      }

      this.seconds--;

      if(this.seconds === 0)
      {
        this.seconds = 60;
        this.minutesRemaining--;
      }
    }, 1000);
  }

  ngOnDestroy() {
    if(this.interval)
      clearInterval(this.interval);
  }
}
