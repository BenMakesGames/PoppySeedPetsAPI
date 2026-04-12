/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, ElementRef, OnDestroy, OnInit, ViewChild} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {HollowEarthSerializationGroup} from "../../../../model/hollow-earth/hollow-earth.serialization-group";
import {HollowEarthPlayerSerializationGroup} from "../../../../model/hollow-earth/hollow-earth-player.serialization-group";
import {SelectPetDialog} from "../../../../dialog/select-pet/select-pet.dialog";
import {UserDataService} from "../../../../service/user-data.service";
import {Subscription} from "rxjs";
import {MessagesService} from "../../../../service/messages.service";
import { HollowEarthTileSerializationGroup } from "../../../../model/hollow-earth/hollow-earth-tile.serialization-group";
import { TileDetailsDialog } from "../../dialog/tile-details/tile-details.dialog";
import { ChangeGoodsDialog } from "../../dialog/change-goods/change-goods.dialog";
import { MatDialog } from "@angular/material/dialog";
import { HasSounds, SoundsService } from "../../../shared/service/sounds.service";

@Component({
    selector: 'app-hollow-earth',
    templateUrl: './hollow-earth.component.html',
    styleUrls: ['./hollow-earth.component.scss'],
    standalone: false
})
@HasSounds([ 'roll-die-a', 'roll-die-b', 'roll-die-c' ])
export class HollowEarthComponent implements OnInit, OnDestroy {

  pageMeta = { title: 'Hollow Earth' };

  @ViewChild('map', { read: ElementRef, 'static': false }) public map: ElementRef<any>;

  @ViewChild('controls') controls: ElementRef;

  player: HollowEarthPlayerSerializationGroup;
  dice: { item: string, image: string, size: number, quantity: number }[];
  loadingResponse = false;
  formattedDescription: string;
  hollowEarthMap: HollowEarthTileSerializationGroup[] = [];
  isOnTradingDepot = false;
  isOnGoods = false;

  hollowEarthAjax: Subscription;

  constructor(
    private api: ApiService, private matDialog: MatDialog, private userData: UserDataService,
    private messages: MessagesService, private readonly sounds: SoundsService
  ) { }

  ngOnInit() {
    this.loadMap(true);
  }

  doSkipMap()
  {
    setTimeout(() => {
      this.controls.nativeElement.focus();
    })
  }

  private loadMap(recenterMapAfterLoad: boolean)
  {
    this.hollowEarthAjax = this.api.get<HollowEarthSerializationGroup>('/hollowEarth').subscribe({
      next: (r: ApiResponseModel<HollowEarthSerializationGroup>) => {
        this.handleHollowEarthResponse(r, recenterMapAfterLoad);
      }
    });
  }

  ngOnDestroy(): void {
    this.hollowEarthAjax.unsubscribe();
  }

  centerMap()
  {
    const mapDimensions = this.map.nativeElement.getBoundingClientRect();
    const x = this.player.currentTile.x * 64 - mapDimensions.width / 2 + 32;
    const y = this.player.currentTile.y * 64 - mapDimensions.height / 2 + 32;
    this.map.nativeElement.scrollLeft = x;
    this.map.nativeElement.scrollTop = y;
  }

  doShowTile(tile: HollowEarthTileSerializationGroup)
  {
    TileDetailsDialog.open(this.matDialog, tile, !this.player.action).afterClosed().subscribe({
      next: r => {
        if(r && r.mapChanged)
          this.loadMap(false);
      }
    });
  }

  doRoll(dieName: string)
  {
    if(this.loadingResponse) return;

    this.loadingResponse = true;

    this.api.post('/hollowEarth/roll', { die: dieName }).subscribe({
      next: (r: ApiResponseModel<HollowEarthSerializationGroup>) => {
        this.sounds.playRandomSound([ 'roll-die-a', 'roll-die-b', 'roll-die-c' ]);

        this.handleHollowEarthResponse(r, true);

        if(Math.random() * 100 <= 1)
          this.messages.addGenericMessage('The Carbuncle ate itself.');
      }
    });
  }

  doChoose(choice: number)
  {
    this.doContinue({
      choice: choice
    });
  }

  doPay()
  {
    this.doContinue({
      payUp: true
    });
  }

  doNotPay()
  {
    this.doContinue({
      payUp: false
    });
  }

  doContinue(data: any = null)
  {
    if(this.loadingResponse) return;

    this.loadingResponse = true;

    this.api.post('/hollowEarth/continue', data).subscribe({
      next: (r: ApiResponseModel<HollowEarthSerializationGroup>) => {
        this.handleHollowEarthResponse(r, true);
      },
      error: () => {
        this.loadingResponse = false;
      }
    });
  }

  private handleHollowEarthResponse(r: ApiResponseModel<HollowEarthSerializationGroup>, recenterMapAfterLoad: boolean)
  {
    this.player = r.data.player;
    this.hollowEarthMap = r.data.map;
    this.dice = r.data.dice.sort((a, b) => a.size - b.size);

    if(this.player.action && this.player.action.description)
      this.formattedDescription = this.formatDescription(this.player.action.description);
    else
      this.formattedDescription = null;

    if(recenterMapAfterLoad)
      setTimeout(() => { this.centerMap(); }, 0);

    this.loadingResponse = false;

    this.isOnTradingDepot = r.data.map.some(tile =>
      tile.isTradingDepot &&
      tile.x === this.player.currentTile.x &&
      tile.y === this.player.currentTile.y
    );

    this.isOnGoods = r.data.map.some(tile =>
      tile.availableGoods && tile.availableGoods.length > 0 &&
      tile.x === this.player.currentTile.x &&
      tile.y === this.player.currentTile.y
    );
  }

  formatDescription(description: string)
  {
    return description
      .replace(/%pet\.name%/g, this.player.chosenPet.name)
      .replace(/%player\.name%/g, this.userData.user.getValue().name)
      .replace(/%player\.basementSize%/g, this.userData.user.getValue().basementSize.toString())
    ;
  }

  doChangeGoods()
  {
    const currentTile = this.hollowEarthMap.find(tile =>
      tile.availableGoods && tile.availableGoods.length > 0 &&
      tile.x === this.player.currentTile.x &&
      tile.y === this.player.currentTile.y
    );

    if(!currentTile)
      return;

    ChangeGoodsDialog.open(this.matDialog, currentTile).afterClosed().subscribe({
      next: v => {
        if(v && v.selectedGoods)
        {
          this.hollowEarthMap = this.hollowEarthMap.map(tile => {
            if(
              tile.availableGoods && tile.availableGoods.length > 0 &&
              tile.x === this.player.currentTile.x &&
              tile.y === this.player.currentTile.y
            )
            {
              return {
                ...tile,
                selectedGoods: v.selectedGoods
              };
            }
            else
              return tile;
          });
        }
      }
    });
  }

  doSwitchPet()
  {
    if(this.loadingResponse) return;

    SelectPetDialog.open(this.matDialog).afterClosed().subscribe({
      next: (pet) => {
        if(pet)
        {
          this.loadingResponse = true;

          this.api.post('/hollowEarth/changePet/' + pet.id).subscribe({
            next: (r: ApiResponseModel<HollowEarthSerializationGroup>) => {
              this.handleHollowEarthResponse(r, false);
            }
          });
        }
      }
    });
  }
}
