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
