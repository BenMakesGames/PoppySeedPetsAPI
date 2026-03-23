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
