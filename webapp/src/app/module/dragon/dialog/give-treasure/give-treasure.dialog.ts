/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, OnDestroy, OnInit} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {DragonTreasureSerializationGroup} from "../../../../model/dragon-treasure.serialization-group";
import {Subscription} from "rxjs";
import {MyDragonSerializationGroup} from "../../../../model/my-dragon-serialization.group";
import { ThemeService } from "../../../shared/service/theme.service";
import { MatDialog, MatDialogRef } from "@angular/material/dialog";
import { ItemOtherPropertiesIcons } from "../../../../model/item-other-properties-icons";

@Component({
    selector: 'app-give-treasure',
    templateUrl: './give-treasure.dialog.html',
    styleUrls: ['./give-treasure.dialog.scss'],
    standalone: false
})
export class GiveTreasureDialog implements OnInit, OnDestroy {

  treasuresSubscription = Subscription.EMPTY;
  treasures: DragonTreasureSerializationGroup[]|null = null;
  coins: any = {};
  sort: any = {};

  lastClicked = [];
  selected: any = {};
  numSelected = 0;

  giveTreasureSubscription = Subscription.EMPTY;

  readonly itemOtherPropertiesIcons = ItemOtherPropertiesIcons;

  constructor(
    private dialogRef: MatDialogRef<GiveTreasureDialog>,
    private api: ApiService, private themeService: ThemeService
  ) {

  }

  ngOnInit(): void {
    this.treasuresSubscription = this.api.get<DragonTreasureSerializationGroup[]>('/dragon/offerings').subscribe({
      next: r => {
        this.treasures = r.data;

        this.treasures.forEach(t => {
          this.sort[t.id] = 0;
          this.coins[t.id] = [];

          for(let i = 0; i < t.item.treasure.gems; i++)
          {
            this.sort[t.id] += 10000;
            this.coins[t.id].push('gems');
          }

          for(let i = 0; i < t.item.treasure.gold; i++)
          {
            this.sort[t.id] += 100;
            this.coins[t.id].push('gold');
          }

          for(let i = 0; i < t.item.treasure.silver; i++)
          {
            this.sort[t.id] += 1;
            this.coins[t.id].push('silver');
          }
        });

        this.treasures.sort((a, b) => this.sort[b.id] - this.sort[a.id]);
      }
    });
  }

  ngOnDestroy(): void {
    this.treasuresSubscription.unsubscribe();
  }

  recordLastClicked(inventory: DragonTreasureSerializationGroup)
  {
    this.lastClicked.push(inventory);

    if(this.lastClicked.length > 2)
      this.lastClicked.shift();
  }

  doLongPress(inventory: DragonTreasureSerializationGroup)
  {
    if(this.themeService.multiSelectWith.getValue() !== 'longPress')
      return;

    this.multiSelect(inventory);
  }

  doDoubleClickItem(inventory: DragonTreasureSerializationGroup)
  {
    if(this.themeService.multiSelectWith.getValue() !== 'doubleClick')
      return;

    if(!this.lastClicked.every(c => c === inventory))
      return;

    this.multiSelect(inventory);
  }

  private multiSelect(treasure: DragonTreasureSerializationGroup)
  {
    if(!this.giveTreasureSubscription.closed) return;

    const select = !this.selected.hasOwnProperty(treasure.id);

    if(select)
    {
      this.treasures.filter(t => t.item.name === treasure.item.name).forEach(t => {
        if(!this.selected.hasOwnProperty(t.id))
        {
          this.selected[t.id] = true;
          this.numSelected++;
        }
      });
    }
    else
    {
      this.treasures.filter(t => t.item.name === treasure.item.name).forEach(t => {
        if(this.selected.hasOwnProperty(t.id)) {
          delete this.selected[t.id];
          this.numSelected--;
        }
      });
    }
  }

  doSelectTreasure(treasure)
  {
    this.recordLastClicked(treasure);

    if(this.selected.hasOwnProperty(treasure.id)) {
      delete this.selected[treasure.id];
      this.numSelected--;
    }
    else
    {
      this.selected[treasure.id] = true;
      this.numSelected++;
    }
  }

  doGiveTreasures()
  {
    this.dialogRef.disableClose = true;

    const data = {
      treasure: Object.keys(this.selected).map(s => parseInt(s))
    };

    this.giveTreasureSubscription = this.api.post<MyDragonSerializationGroup>('/dragon/giveTreasure', data).subscribe({
      next: (r) => {
        this.dialogRef.close({
          dragon: r.data
        });
      },
      error: () => {
        this.dialogRef.disableClose = false;
      }
    })
  }

  public static open(matDialog: MatDialog)
  {
    return matDialog.open(GiveTreasureDialog);
  }
}
