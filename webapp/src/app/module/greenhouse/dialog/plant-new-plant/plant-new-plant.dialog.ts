/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, Inject, OnDestroy, OnInit} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {MySeedsSerializationGroup} from "../../../../model/greenhouse/my-seeds.serialization-group";
import {GreenhousePlantTypeEnum} from "../../../../model/greenhouse-plant-type.enum";
import {Subscription} from "rxjs";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";
import { ItemOtherPropertiesIcons } from "../../../../model/item-other-properties-icons";
import { DialogTitleWithIconsComponent } from "../../../shared/component/dialog-title-with-icons/dialog-title-with-icons.component";
import { LoadingThrobberComponent } from "../../../shared/component/loading-throbber/loading-throbber.component";
import { InventoryItemComponent } from "../../../shared/component/inventory-item/inventory-item.component";
import { TitleCasePipe } from "@angular/common";

@Component({
  templateUrl: './plant-new-plant.dialog.html',
  imports: [
    DialogTitleWithIconsComponent,
    LoadingThrobberComponent,
    InventoryItemComponent,
    TitleCasePipe
  ],
  styleUrls: [ './plant-new-plant.dialog.scss' ]
})
export class PlantNewPlantDialog implements OnInit, OnDestroy {

  plantingSeed = false;
  seeds: SeedGroup[];
  plantType: GreenhousePlantTypeEnum;

  getSeedsAjax: Subscription;
  seedlingIcon = '';

  constructor(
    private dialogRef: MatDialogRef<PlantNewPlantDialog>, private api: ApiService,
    @Inject(MAT_DIALOG_DATA) private data: any
  ) {
    this.plantType = data.type;

    switch(this.plantType)
    {
      case 'earth': this.seedlingIcon = ItemOtherPropertiesIcons.SeedlingEarth; break;
      case 'dark': this.seedlingIcon = ItemOtherPropertiesIcons.SeedlingDark; break;
      case 'water': this.seedlingIcon = ItemOtherPropertiesIcons.SeedlingWater; break;
    }
  }

  ngOnInit() {
    this.getSeedsAjax = this.api.get<MySeedsSerializationGroup[]>('/greenhouse/seeds/' + this.plantType).subscribe((r: ApiResponseModel<MySeedsSerializationGroup[]>) => {
      this.seeds = r.data
        .filter((i, index, self) => {
          return self.findIndex(i2 =>
            i2.item.name === i.item.name
          ) === index;
        })
        .map(i => {
          return {
            quantity: r.data.filter(i2 => i2.item.name === i.item.name).length,
            ... i,
          }
        })
        .sort((f1, f2) => f1.item.name.localeCompare(f2.item.name))
      ;
    });
  }

  ngOnDestroy(): void {
    this.getSeedsAjax.unsubscribe();
  }

  doPlant(seed)
  {
    this.plantingSeed = true;

    this.api.post('/greenhouse/plantSeed', { seed: seed.id }).subscribe({
      next: r => {
        this.dialogRef.close({ greenhouse: r.data });
      },
      error: () => {
        this.plantingSeed = false;
      }
    });
  }

  public static open(matDialog: MatDialog, type: GreenhousePlantTypeEnum): MatDialogRef<PlantNewPlantDialog>
  {
    return matDialog.open(PlantNewPlantDialog, {
      data: {
        type: type
      },
      width: '3in'
    });
  }
}

interface SeedGroup
{
  quantity: number;
  id: number;
  item: { name: string, image: string };
}
