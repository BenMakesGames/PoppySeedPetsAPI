/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, Inject, OnDestroy} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {MyInventorySerializationGroup} from "../../../../model/my-inventory/my-inventory.serialization-group";
import {Subscription} from "rxjs";
import { ThemeService } from "../../../shared/service/theme.service";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";

@Component({
    templateUrl: './feed-whelp.dialog.html',
    styleUrls: ['./feed-whelp.dialog.scss'],
    standalone: false
})
export class FeedWhelpDialog implements OnDestroy {

  loading = false;
  food: MyInventorySerializationGroup[]|null = null;
  selected: any = {};
  numSelected = 0;
  whelpName: string;
  whelpFoodAjax: Subscription;
  lastClicked = [];

  constructor(
    private dialogRef: MatDialogRef<FeedWhelpDialog>,
    private api: ApiService, private themeService: ThemeService,
    @Inject(MAT_DIALOG_DATA) private data: { whelpName: string },
  )
  {
    this.whelpName = data.whelpName;

    this.whelpFoodAjax = this.api.get<MyInventorySerializationGroup[]>('/fireplace/whelpFood').subscribe({
      next: (r: ApiResponseModel<MyInventorySerializationGroup[]>) => {
        this.food = r.data;
      }
    })
  }

  ngOnDestroy(): void {
    this.whelpFoodAjax.unsubscribe();
  }

  recordLastClicked(inventory)
  {
    this.lastClicked.push(inventory);

    if(this.lastClicked.length > 2)
      this.lastClicked.shift();
  }

  doLongPress(inventory: MyInventorySerializationGroup)
  {
    if(this.themeService.multiSelectWith.getValue() !== 'longPress')
      return;

    this.multiSelect(inventory);
  }

  doDoubleClickItem(inventory: MyInventorySerializationGroup)
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
      this.food.filter(f => f.item.name === selected.item.name).forEach(f => {
        if(!this.selected.hasOwnProperty(f.id))
        {
          this.selected[f.id] = true;
          this.numSelected++;
        }
      });
    }
    else
    {
      this.food.filter(f => f.item.name === selected.item.name).forEach(f => {
        if(this.selected.hasOwnProperty(f.id)) {
          delete this.selected[f.id];
          this.numSelected--;
        }
      });
    }
  }

  doSelectFood(fuel)
  {
    if(this.selected.hasOwnProperty(fuel.id)) {
      delete this.selected[fuel.id];
      this.numSelected--;
    }
    else
    {
      this.selected[fuel.id] = true;
      this.numSelected++;
    }
  }

  doFeedWhelp()
  {
    if(this.loading || this.numSelected === 0) return;

    this.loading = true;

    this.api.post('/fireplace/feedWhelp', { food: Object.keys(this.selected) }).subscribe({
      next: (r) => {
        this.dialogRef.close({
          whelp: r.data
        });
      },
      error: () => {
        this.loading = false;
      }
    })

  }

  public static open(matDialog: MatDialog, whelpName: string): MatDialogRef<FeedWhelpDialog>
  {
    return matDialog.open(FeedWhelpDialog, {
      data: {
        whelpName: whelpName
      }
    });
  }
}
