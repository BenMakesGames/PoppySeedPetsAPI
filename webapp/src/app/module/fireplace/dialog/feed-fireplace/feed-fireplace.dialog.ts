/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, OnDestroy} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {FireplaceFuelSerializationGroup} from "../../../../model/fireplace/fireplace-fuel.serialization-group";
import {MyFireplaceSerializationGroup} from "../../../../model/fireplace/my-fireplace.serialization-group";
import {Subscription} from "rxjs";
import { ThemeService } from "../../../shared/service/theme.service";
import { MatDialog, MatDialogRef } from "@angular/material/dialog";
import { ItemOtherPropertiesIcons } from "../../../../model/item-other-properties-icons";

@Component({
    templateUrl: './feed-fireplace.dialog.html',
    styleUrls: ['./feed-fireplace.dialog.scss'],
    standalone: false
})
export class FeedFireplaceDialog implements OnDestroy {

  lastClicked = [];
  loading = false;
  fuels: FireplaceFuelSerializationGroup[] = null;
  selected: any = {};
  numSelected = 0;

  fireplaceFuelAjax: Subscription;

  readonly itemOtherPropertiesIcons = ItemOtherPropertiesIcons;

  constructor(
    private dialogRef: MatDialogRef<FeedFireplaceDialog>,
    private api: ApiService, private themeService: ThemeService,
  )
  {
    this.fireplaceFuelAjax = this.api.get<FireplaceFuelSerializationGroup[]>('/fireplace/fuel').subscribe({
      next: (r: ApiResponseModel<FireplaceFuelSerializationGroup[]>) => {
        this.fuels = r.data;
      }
    })
  }

  ngOnDestroy(): void {
    this.fireplaceFuelAjax.unsubscribe();
  }

  recordLastClicked(inventory: FireplaceFuelSerializationGroup)
  {
    this.lastClicked.push(inventory);

    if(this.lastClicked.length > 2)
      this.lastClicked.shift();
  }

  doLongPress(inventory: FireplaceFuelSerializationGroup)
  {
    if(this.themeService.multiSelectWith.getValue() !== 'longPress')
      return;

    this.multiSelect(inventory);
  }

  doDoubleClickItem(inventory: FireplaceFuelSerializationGroup)
  {
    if(this.themeService.multiSelectWith.getValue() !== 'doubleClick')
      return;

    if(!this.lastClicked.every(c => c === inventory))
      return;

    this.multiSelect(inventory);
  }

  private multiSelect(fuel: FireplaceFuelSerializationGroup)
  {
    if(this.loading) return;

    const select = !this.selected.hasOwnProperty(fuel.id);

    if(select)
    {
      this.fuels.filter(f => f.item.name === fuel.item.name).forEach(f => {
        if(!this.selected.hasOwnProperty(f.id))
        {
          this.selected[f.id] = true;
          this.numSelected++;
        }
      });
    }
    else
    {
      this.fuels.filter(f => f.item.name === fuel.item.name).forEach(f => {
        if(this.selected.hasOwnProperty(f.id)) {
          delete this.selected[f.id];
          this.numSelected--;
        }
      });
    }
  }

  doSelectFuel(fuel: FireplaceFuelSerializationGroup)
  {
    this.recordLastClicked(fuel);

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

  doFeedFireplace()
  {
    if(this.loading || this.numSelected === 0) return;

    this.loading = true;

    this.api.post<MyFireplaceSerializationGroup>('/fireplace/feed', { fuel: Object.keys(this.selected) }).subscribe({
      next: (r: ApiResponseModel<MyFireplaceSerializationGroup>) => {
        this.dialogRef.close(r.data);
      },
      error: () => {
        this.loading = false;
      }
    })

  }

  public static open(matDialog: MatDialog): MatDialogRef<FeedFireplaceDialog>
  {
    return matDialog.open(FeedFireplaceDialog)
  }
}
