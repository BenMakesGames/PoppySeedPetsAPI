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
import { FertilizerSerializationGroup } from "../../../../model/fertilizer.serialization-group";
import { ThemeService } from "../../../shared/service/theme.service";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";
import { ItemOtherPropertiesIcons } from "../../../../model/item-other-properties-icons";
import { DialogTitleWithIconsComponent } from "../../../shared/component/dialog-title-with-icons/dialog-title-with-icons.component";
import { InventoryItemComponent } from "../../../shared/component/inventory-item/inventory-item.component";
import { FertilizerRatingPipe } from "../../pipe/fertilizer-rating.pipe";

@Component({
  templateUrl: './feed-composter.dialog.html',
  imports: [
    DialogTitleWithIconsComponent,
    InventoryItemComponent,
    FertilizerRatingPipe
  ],
  styleUrls: [ './feed-composter.dialog.scss' ]
})
export class FeedComposterDialog {

  readonly FORBIDDEN_COMPOST = [
    'Small Bag of Fertilizer',
    'Bag of Fertilizer',
    'Large Bag of Fertilizer',
    'Twilight Fertilizer'
  ];

  lastClicked = [];
  fertilizer: FertilizerSerializationGroup[] = [];
  selected: any = {};
  numSelected = 0;

  readonly itemOtherPropertiesIcons = ItemOtherPropertiesIcons;

  constructor(
    private dialogRef: MatDialogRef<FeedComposterDialog>,
    private api: ApiService, private themeService: ThemeService,
    @Inject(MAT_DIALOG_DATA) private data: any,
  )
  {
    this.fertilizer = this.data.fertilizer
      .filter(f => this.FORBIDDEN_COMPOST.indexOf(f.item.name) === -1)
      .sort((f1, f2) => {
        if(f1.fertilizerRating === f2.fertilizerRating)
          return f1.item.name.localeCompare(f2.item.name);
        else
          return f2.fertilizerRating - f1.fertilizerRating;
      })
    ;
  }

  recordLastClicked(inventory: FertilizerSerializationGroup)
  {
    this.lastClicked.push(inventory);

    if(this.lastClicked.length > 2)
      this.lastClicked.shift();
  }

  doLongPress(inventory: FertilizerSerializationGroup)
  {
    if(this.themeService.multiSelectWith.getValue() !== 'longPress')
      return;

    this.multiSelect(inventory);
  }

  doDoubleClickItem(inventory: FertilizerSerializationGroup)
  {
    if(this.themeService.multiSelectWith.getValue() !== 'doubleClick')
      return;

    if(!this.lastClicked.every(c => c === inventory))
      return;

    this.multiSelect(inventory);
  }

  private multiSelect(selected)
  {
    const select = !this.selected.hasOwnProperty(selected.id);

    if(select)
    {
      this.fertilizer.filter(f => f.item.name === selected.item.name && f.fertilizerRating > 0).forEach(f => {
        if(!this.selected.hasOwnProperty(f.id))
        {
          this.selected[f.id] = true;
          this.numSelected++;
        }
      });
    }
    else
    {
      this.fertilizer.filter(f => f.item.name === selected.item.name).forEach(f => {
        if(this.selected.hasOwnProperty(f.id)) {
          delete this.selected[f.id];
          this.numSelected--;
        }
      });
    }
  }

  doSelectFertilizer(fertilizer: FertilizerSerializationGroup)
  {
    this.recordLastClicked(fertilizer);

    if(this.selected.hasOwnProperty(fertilizer.id)) {
      delete this.selected[fertilizer.id];
      this.numSelected--;
    }
    else
    {
      this.selected[fertilizer.id] = true;
      this.numSelected++;
    }
  }

  doFeedComposter()
  {
    this.dialogRef.close(Object.keys(this.selected));
  }

  public static open(matDialog: MatDialog, fertilizer: FertilizerSerializationGroup[]): MatDialogRef<FeedComposterDialog>
  {
    return matDialog.open(FeedComposterDialog, {
      data: {
        fertilizer: fertilizer
      }
    });
  }
}
