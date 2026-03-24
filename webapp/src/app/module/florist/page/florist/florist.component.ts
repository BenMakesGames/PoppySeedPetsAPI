import { Component, OnInit } from '@angular/core';
import {Subscription} from "rxjs";
import {UserDataService} from "../../../../service/user-data.service";
import { MyAccountSerializationGroup } from "../../../../model/my-account/my-account.serialization-group";
import { ApiService } from "../../../shared/service/api.service";
import { ItemDetailsDialog } from "../../../../dialog/item-details/item-details.dialog";
import { MatDialog } from "@angular/material/dialog";
import { HasSounds, SoundsService } from "../../../shared/service/sounds.service";

@Component({
    templateUrl: './florist.component.html',
    styleUrls: ['./florist.component.scss'],
    standalone: false
})
@HasSounds([ 'chaching', 'coins' ])
export class FloristComponent implements OnInit {
  pageMeta = { title: 'The Florist' };

  DialogStepEnum = DialogStepEnum;

  user: MyAccountSerializationGroup;
  userSubscription: Subscription;

  step = DialogStepEnum.Shop;
  loading = false;

  tradingForGiftPackage = Subscription.EMPTY;
  inventorySubscription = Subscription.EMPTY;
  inventory: FloristStoreInventorySerializationGroup[]|undefined;
  canTradeForGiftPackage = false;

  constructor(
    private userData: UserDataService, private api: ApiService, private matDialog: MatDialog,
    private sounds: SoundsService
  ) {
  }

  ngOnInit()
  {
    this.userSubscription = this.userData.user.subscribe(u => {
      this.user = u;
    });

    this.inventorySubscription = this.api.get<FloristResponse>('/florist').subscribe({
      next: r => {
        this.inventory = r.data.inventory;
        this.canTradeForGiftPackage = r.data.canTradeForGiftPackage;
      }
    })
  }

  ngOnDestroy()
  {
    this.userSubscription.unsubscribe();
    this.inventorySubscription.unsubscribe();
  }

  doTradeForGiftPackage()
  {
    if(this.loading) return;

    this.loading = true;

    this.api.post('/florist/tradeForGiftPackage').subscribe({
      next: () => {
        this.sounds.playSound('coins');
        this.step = DialogStepEnum.AfterShop;
        this.loading = false;
      },
      error: () => {
        this.loading = false;
      }
    });
  }

  doViewItem(itemName: string)
  {
    ItemDetailsDialog.open(this.matDialog, itemName);
  }

  doBuy(itemName: string)
  {
    if(this.loading) return;

    this.loading = true;

    this.api.post('/florist/buy', { item: itemName }).subscribe({
      next: () => {
        this.sounds.playSound('chaching');
        this.step = DialogStepEnum.AfterShop;
        this.loading = false;
      },
      error: () => {
        this.loading = false;
      }
    })
  }
}

enum DialogStepEnum
{
  Shop,
  AskForExplanation,
  AfterShop
}

interface FloristResponse
{
  inventory: FloristStoreInventorySerializationGroup[];
  canTradeForGiftPackage: boolean;
}

interface FloristStoreInventorySerializationGroup
{
  item: { name: string, image: string };
  cost: number;
}