import { Component, OnDestroy, OnInit } from '@angular/core';
import { ApiService } from "../../../shared/service/api.service";
import { Subscription } from "rxjs";
import { ItemDetailsDialog } from "../../../../dialog/item-details/item-details.dialog";
import { MatDialog } from "@angular/material/dialog";

@Component({
    templateUrl: './gift-shop.component.html',
    styleUrls: ['./gift-shop.component.scss'],
    standalone: false
})
export class GiftShopComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Museum - Gift Shop' };

  giftShopSubscription = Subscription.EMPTY;
  giftShop: GiftShopResponse|null = null;
  buying = false;
  tab = '';

  constructor(private api: ApiService, private matDialog: MatDialog) { }

  ngOnInit(): void {
    this.giftShopSubscription = this.api.get<GiftShopResponse>('/museum/giftShop').subscribe({
      next: r => {
        this.processGiftShopResponse(r.data);

        this.tab = this.giftShop.giftShop[0].category;
      }
    })
  }

  ngOnDestroy() {
    this.giftShopSubscription.unsubscribe();
  }

  private processGiftShopResponse(data: GiftShopResponse)
  {
    this.giftShop = data;

    this.giftShop.giftShop = this.giftShop.giftShop
      .sort((a, b) => a.category.localeCompare(b.category))
      .map(s => {
        return {
          ...s,
          inventory: s.inventory.sort((a, b) => a.item.name.localeCompare(b.item.name))
        };
      })
    ;
  }

  doViewItem(itemName: string)
  {
    ItemDetailsDialog.open(this.matDialog, itemName);
  }

  doBuy(category: string, itemName: string)
  {
    if(this.buying) return;

    this.buying = true;

    this.api.post<GiftShopResponse>('/museum/giftShop/buy', { category: category, item: itemName }).subscribe({
      next: r => {
        this.processGiftShopResponse(r.data);
        this.buying = false;
      },
      error: () => {
        this.buying = false;
      }
    })
  }

}

interface GiftShopResponse
{
  pointsAvailable: number;
  giftShop: {
    category: string;
    itemsDonated: number;
    requiredToUnlock: number;
    inventory: GiftShopItemCost[]
  }[];
}

interface GiftShopItemCost
{
  item: { name: string, image: string };
  cost: number;
}