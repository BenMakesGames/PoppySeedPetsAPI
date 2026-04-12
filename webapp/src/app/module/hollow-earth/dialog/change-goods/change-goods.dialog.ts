/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, Inject, OnInit } from '@angular/core';
import { HollowEarthTileSerializationGroup } from "../../../../model/hollow-earth/hollow-earth-tile.serialization-group";
import { ApiService } from "../../../shared/service/api.service";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";

@Component({
    templateUrl: './change-goods.dialog.html',
    styleUrls: ['./change-goods.dialog.scss'],
    standalone: false
})
export class ChangeGoodsDialog implements OnInit {

  tile: HollowEarthTileSerializationGroup;
  selectedGoods: string|null;
  loading = false;

  constructor(
    private dialogRef: MatDialogRef<ChangeGoodsDialog>,
    private api: ApiService,
    @Inject(MAT_DIALOG_DATA) private data,
  ) {
    this.tile = data.tile;
    this.selectedGoods = this.tile.selectedGoods;
  }

  ngOnInit(): void {
  }

  public doClose()
  {
    this.dialogRef.close();
  }

  public doChangeGood()
  {
    if(this.loading)
      return;

    this.loading = true;
    this.dialogRef.disableClose = true;

    this.api.post('/hollowEarth/changeTileGoods', { goods: this.selectedGoods }).subscribe({
      next: () => {
        this.dialogRef.close({ selectedGoods: this.selectedGoods });
      },
      error: () => {
        this.loading = false;
        this.dialogRef.disableClose = false;
      }
    })
  }

  public static open(matDialog: MatDialog, hollowEarthTile: HollowEarthTileSerializationGroup): MatDialogRef<ChangeGoodsDialog>
  {
    return matDialog.open(ChangeGoodsDialog, {
      data: {
        tile: hollowEarthTile
      }
    })
  }

}
