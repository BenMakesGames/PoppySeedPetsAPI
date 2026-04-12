/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, Inject } from '@angular/core';
import { ApiService } from "../../../shared/service/api.service";
import { HollowEarthTileSerializationGroup } from "../../../../model/hollow-earth/hollow-earth-tile.serialization-group";
import { ApiResponseModel } from "../../../../model/api-response.model";
import { Subscription } from "rxjs";
import { UserDataService } from "../../../../service/user-data.service";
import { MyAccountSerializationGroup } from "../../../../model/my-account/my-account.serialization-group";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";

@Component({
    templateUrl: './select-tile.dialog.html',
    styleUrls: ['./select-tile.dialog.scss'],
    standalone: false
})
export class SelectTileDialog {

  tile: HollowEarthTileSerializationGroup;
  myTiles: any[]|null = null;
  myTilesAjax = Subscription.EMPTY;
  useTileSubscription = Subscription.EMPTY;
  user: MyAccountSerializationGroup;

  hasSameGroup = (i, i2) => i2.item.name === i.item.name && (!!i2.sellPrice) === (!!i.sellPrice);

  constructor(
    private dialogRef: MatDialogRef<SelectTileDialog>,
    private api: ApiService, private userData: UserDataService,
    @Inject(MAT_DIALOG_DATA) private data,
  ) {
    this.user = userData.user.getValue();
    this.tile = data.tile;

    const requestData = {
      types: this.tile.types
    }

    this.myTilesAjax = this.api.get<any[]>('/hollowEarth/myTiles', requestData).subscribe({
      next: (r: ApiResponseModel<any[]>) => {
        this.myTiles = r.data
          .filter((i, index, self) => {
            return self.findIndex(i2 => this.hasSameGroup(i, i2)) === index;
          })
          .map(i => {
            return {
              quantity: r.data.filter(i2 => this.hasSameGroup(i, i2)).length,
              ... i,
            }
          })
          .sort((f1, f2) => f1.item.name.localeCompare(f2.item.name))
        ;
      }
    })
  }

  public doClose()
  {
    this.dialogRef.close({ close: true });
  }

  doSelectTile(inventoryItem: any)
  {
    const data = {
      tile: this.tile.id,
      item: inventoryItem.id,
    };

    this.dialogRef.disableClose = true;

    this.useTileSubscription = this.api.post('/hollowEarth/setTileCard', data).subscribe({
      next: () => {
        this.dialogRef.close({ usedTile: true });
      },
      error: () => {
        this.dialogRef.disableClose = false;
      }
    });
  }

  public static open(matDialog: MatDialog, hollowEarthTile: HollowEarthTileSerializationGroup): MatDialogRef<SelectTileDialog>
  {
    return matDialog.open(SelectTileDialog, {
      data: {
        tile: hollowEarthTile
      }
    })
  }

}
