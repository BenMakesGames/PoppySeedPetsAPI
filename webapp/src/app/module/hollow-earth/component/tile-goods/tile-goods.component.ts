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
