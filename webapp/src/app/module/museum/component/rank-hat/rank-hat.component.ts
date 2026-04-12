/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, Input, OnChanges, SimpleChanges } from '@angular/core';

@Component({
    selector: 'app-rank-hat',
    templateUrl: './rank-hat.component.html',
    styleUrls: ['./rank-hat.component.scss'],
    standalone: false
})
export class RankHatComponent implements OnChanges {

  @Input() rank: number;

  hat: { image: string, translateX: number, translateY: number, scaleX: number }|undefined;

  constructor() { }

  ngOnChanges(changes: SimpleChanges) {
    this.hat = RankHatComponent.getHat(this.rank);
  }

  static getHat(rank: number): { image: string, translateX: number, translateY: number, scaleX: number }|undefined
  {
    if(rank === 1)
      return { image: 'hat/triple-crown', translateX: 0, translateY: 12.5, scaleX: 1 };

    if(rank <= 20)
      return { image: 'hat/crown', translateX: 0, translateY: 12.5, scaleX: 1 };

    if(rank <= 40)
      return { image: 'hat/feathered-red-black', translateX: -3, translateY: 22, scaleX: 1 };

    if(rank <= 60)
      return { image: 'hat/top-hat-eccentric', translateX: 0, translateY: 30, scaleX: -1 };

    if(rank <= 80)
      return { image: 'hat/jester-cap', translateX: 0, translateY: 25, scaleX: 1 };

    if(rank <= 100)
      return { image: 'hat/coconut-half', translateX: 0, translateY: 22, scaleX: 1 };

    return undefined;
  }

}
