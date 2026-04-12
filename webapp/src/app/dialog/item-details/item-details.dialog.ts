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
import {UserDataService} from "../../service/user-data.service";
import { isFeatureUnlocked, MyAccountSerializationGroup } from "../../model/my-account/my-account.serialization-group";
import {ItemEncyclopediaSerializationGroup} from "../../model/encyclopedia/item-encyclopedia.serialization-group";
import {ApiService} from "../../module/shared/service/api.service";
import {ApiResponseModel} from "../../model/api-response.model";
import {Subscription} from "rxjs";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";
import { RouterLink } from "@angular/router";
import { LoadingThrobberComponent } from "../../module/shared/component/loading-throbber/loading-throbber.component";
import { ItemPriceHistoryComponent } from "../../module/shared/component/item-price-history/item-price-history.component";
import { ItemDetailsComponent } from "../../module/shared/component/item-details/item-details.component";
import { CommonModule } from "@angular/common";
import { ItemPriceHistoryFromApiComponent } from "../../module/shared/component/item-price-history-from-api/item-price-history-from-api.component";

@Component({
    templateUrl: './item-details.dialog.html',
    imports: [
        RouterLink,
        LoadingThrobberComponent,
        ItemDetailsComponent,
        CommonModule,
        ItemPriceHistoryFromApiComponent
    ],
    styleUrls: ['./item-details.dialog.scss']
})
export class ItemDetailsDialog implements OnInit, OnDestroy {

  user: MyAccountSerializationGroup;
  item: ItemEncyclopediaSerializationGroup;

  showMarketSearch = true;
  showBasementSearch = true;
  showMarketHistory = false;
  bonus = null;
  spice = null;

  itemName: string;

  itemInfoAjax: Subscription;

  constructor(
    private userData: UserDataService,
    private api: ApiService,
    private dialogRef: MatDialogRef<ItemDetailsDialog>,
    @Inject(MAT_DIALOG_DATA) private data: any,
  ) {
    this.itemName = data.itemName;

    this.showMarketSearch = !this.data.options || !this.data.options.hideMarketSearch;
    this.showBasementSearch = !this.data.options || !this.data.options.hideBasementSearch;
    this.showMarketHistory = this.data.options && this.data.options.showMarketHistory;
    this.bonus = this.data.options?.bonus;
    this.spice = this.data.options?.spice;
  }

  ngOnInit() {
    this.user = this.userData.user.getValue();

    if(!isFeatureUnlocked(this.user, 'Basement')) this.showBasementSearch = false;
    if(!isFeatureUnlocked(this.user, 'Market')) this.showMarketSearch = false;

    this.itemInfoAjax = this.api.get<ItemEncyclopediaSerializationGroup>('/encyclopedia/item/' + this.itemName).subscribe({
      next: (r: ApiResponseModel<ItemEncyclopediaSerializationGroup>) => {
        this.item = r.data;
      }
    });
  }

  ngOnDestroy(): void {
    this.itemInfoAjax.unsubscribe();
  }

  doCloseDialog()
  {
    this.dialogRef.close();
  }

  static open(matDialog: MatDialog, itemName: string, options: ItemDetailsDialogOptions = null)
  {
    return matDialog.open(ItemDetailsDialog, {
      data: {
        itemName: itemName,
        options: options,
      },
      width: '5in'
    });
  }
}

export interface ItemDetailsDialogOptions
{
  hideMarketSearch?: boolean;
  hideBasementSearch?: boolean;
  showMarketHistory?: boolean;
  bonus?: { id: number, name: string, isSuffix: boolean, effects: any };
  spice?: { id: number, name: string, isSuffix: boolean, effects: any };
}
