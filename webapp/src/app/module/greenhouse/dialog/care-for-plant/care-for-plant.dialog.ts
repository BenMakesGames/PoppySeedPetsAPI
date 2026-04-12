/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, Inject} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";
import { FertilizerSerializationGroup } from "../../../../model/fertilizer.serialization-group";
import { ItemOtherPropertiesIcons } from "../../../../model/item-other-properties-icons";
import { DialogTitleWithIconsComponent } from "../../../shared/component/dialog-title-with-icons/dialog-title-with-icons.component";
import { InventoryItemComponent } from "../../../shared/component/inventory-item/inventory-item.component";
import { FertilizerRatingPipe } from "../../pipe/fertilizer-rating.pipe";

@Component({
  templateUrl: './care-for-plant.dialog.html',
  imports: [
    DialogTitleWithIconsComponent,
    InventoryItemComponent,
    FertilizerRatingPipe
  ],
  styleUrls: [ './care-for-plant.dialog.scss' ]
})
export class CareForPlantDialog {

  loading = false;
  plantId: number;
  fertilizers: FertilizerSerializationGroupWithQuantity[];

  hasSameGroup = (i, i2) =>
    i2.item.name === i.item.name &&
    (!!i2.sellPrice) === (!!i.sellPrice) &&
    i2.enchantment?.id === i.enchantment?.id &&
    i2.spice?.id == i.spice?.id
  ;

  readonly itemOtherPropertiesIcons = ItemOtherPropertiesIcons;

  constructor(
    private dialogRef: MatDialogRef<CareForPlantDialog>,
    @Inject(MAT_DIALOG_DATA) private data: any,
    private api: ApiService,
  )
  {
    this.plantId = data.plantId;
    this.fertilizers = data.fertilizers
      .filter((i, index, self) => {
        return self.findIndex(i2 => this.hasSameGroup(i, i2)) === index;
      })
      .map(i => {
        return {
          quantity: data.fertilizers.filter(i2 => this.hasSameGroup(i, i2)).length,
          ... i,
        }
      })
      .sort((f1, f2) => {
        if(f1.fertilizerRating === f2.fertilizerRating)
          return f1.item.name.localeCompare(f2.item.name);
        else
          return f2.fertilizerRating - f1.fertilizerRating;
      })
    ;
  }

  doSelectFertilizer(fertilizer)
  {
    if(this.loading) return;

    this.loading = true;

    this.api.post('/greenhouse/' + this.plantId + '/fertilize', { fertilizer: fertilizer.id }).subscribe({
      next: r => {
        this.dialogRef.close({ greenhouse: r.data });
      },
      error: () => {
        this.loading = false;
      }
    })
  }

  public static open(matDialog: MatDialog, plantId: number, fertilizers): MatDialogRef<CareForPlantDialog>
  {
    return matDialog.open(CareForPlantDialog, {
      data: {
        plantId: plantId,
        fertilizers: fertilizers
      }
    })
  }
}

interface FertilizerSerializationGroupWithQuantity extends FertilizerSerializationGroup
{
  quantity: number;
}