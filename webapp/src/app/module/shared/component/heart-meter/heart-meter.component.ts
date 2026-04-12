/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, Input, OnChanges } from '@angular/core';
import { CommonModule } from "@angular/common";

@Component({
    selector: 'app-heart-meter',
    templateUrl: './heart-meter.component.html',
    imports: [
        CommonModule
    ],
    styleUrls: ['./heart-meter.component.scss']
})
export class HeartMeterComponent implements OnChanges {

  @Input() commitment = 0;

  meter = [ 'e', 'e', 'e', 'e', 'e' ];

  ngOnChanges() {
    for(let i = 0; i < 5; i++)
    {
      if(i + 1 <= this.commitment)
        this.meter[i] = 'f';
      else if(i < this.commitment)
        this.meter[i] = 'h';
      else
        this.meter[i] = 'e';
    }
  }
}
