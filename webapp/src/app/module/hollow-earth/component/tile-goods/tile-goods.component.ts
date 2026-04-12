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
import {
  HollowEarthDirection,
  HollowEarthTileSerializationGroup
} from "../../../../model/hollow-earth/hollow-earth-tile.serialization-group";

@Component({
    selector: 'app-tile-goods',
    templateUrl: './tile-goods.component.html',
    styleUrls: ['./tile-goods.component.scss'],
    standalone: false
})
export class TileGoodsComponent implements OnChanges {

  @Input() tile: HollowEarthTileSerializationGroup;

  selectedGood = 'none';
  selectedGoodDescription = 'No goods selected.';
  x = 0;
  y = 0;

  constructor() { }

  ngOnChanges(changes: SimpleChanges): void {
    if(this.tile.selectedGoods === null)
    {
      this.selectedGood = 'none';
      this.selectedGoodDescription = 'No goods selected.';
    }
    else
    {
      this.selectedGood = this.tile.selectedGoods;
      this.selectedGoodDescription = this.tile.selectedGoods + ' produced here.';
    }

    this.x = this.tile.x * 64 + 32 - 8;
    this.y = this.tile.y * 64 + 32 - 8;

    if(this.tile.goodsSide === HollowEarthDirection.N)
      this.y -= 44;
    else if(this.tile.goodsSide === HollowEarthDirection.E)
      this.x += 44;
    else if(this.tile.goodsSide === HollowEarthDirection.S)
      this.y += 44;
    else if(this.tile.goodsSide === HollowEarthDirection.W)
      this.x -= 44;
  }

}
