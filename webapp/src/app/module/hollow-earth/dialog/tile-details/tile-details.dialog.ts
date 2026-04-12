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
import { HollowEarthTileSerializationGroup } from "../../../../model/hollow-earth/hollow-earth-tile.serialization-group";
import { ApiService } from "../../../shared/service/api.service";
import { SelectTileDialog } from "../select-tile/select-tile.dialog";
import { AreYouSureDialog } from "../../../../dialog/are-you-sure/are-you-sure.dialog";
import { Subscription } from "rxjs";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";

@Component({
    templateUrl: './tile-details.dialog.html',
    styleUrls: ['./tile-details.dialog.scss'],
    standalone: false
})
export class TileDetailsDialog {

  tile: HollowEarthTileSerializationGroup;
  deleteTileSubscription = Subscription.EMPTY;
  canEdit: boolean;

  constructor(
    private dialogRef: MatDialogRef<TileDetailsDialog>,
    private api: ApiService,
    private matDialog: MatDialog,
    @Inject(MAT_DIALOG_DATA) private data,
  ) {
    this.tile = data.tile;
    this.canEdit = data.canEdit;
  }

  doClearTile()
  {
    AreYouSureDialog.open(this.matDialog, 'Are You Sure?', 'Really clear the "' + this.tile.name + '" space?').afterClosed().subscribe({
      next: confirmed => {
        if(confirmed)
        {
          this.dialogRef.disableClose = true;

          this.deleteTileSubscription = this.api.post('/hollowEarth/removeTileCard', { tile: this.tile.id }).subscribe({
            next: () => {
              this.dialogRef.close({ mapChanged: true });
            },
            error: () => {
              this.dialogRef.disableClose = false;
            }
          });
        }
      }
    })
  }

  doPlaceTile()
  {
    SelectTileDialog.open(this.matDialog, this.tile).afterClosed().subscribe({
      next: r => {
        if(r)
        {
          if(r.usedTile)
            this.dialogRef.close({ mapChanged: true });
          else if(r.close)
            this.dialogRef.close();
        }
      }
    });
  }

  doClose()
  {
    this.dialogRef.close();
  }

  public static open(matDialog: MatDialog, tile: HollowEarthTileSerializationGroup, canEdit: boolean): MatDialogRef<TileDetailsDialog>
  {
    return matDialog.open(TileDetailsDialog, {
      data: {
        tile: tile,
        canEdit: canEdit
      }
    });
  }
}
