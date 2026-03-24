import { Component, Inject, OnDestroy, OnInit } from '@angular/core';
import { MyInventorySerializationGroup } from "../../../../model/my-inventory/my-inventory.serialization-group";
import { MyAccountSerializationGroup } from "../../../../model/my-account/my-account.serialization-group";
import { Subscription } from "rxjs";
import { UserDataService } from "../../../../service/user-data.service";
import { ApiService } from "../../service/api.service";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";
import { InventoryItemComponent } from "../../component/inventory-item/inventory-item.component";
import { MoneysComponent } from "../../component/moneys/moneys.component";
import { CommonModule } from "@angular/common";
import { CeilPipe } from "../../pipe/ceil.pipe";
import { FormsModule } from "@angular/forms";
import { RouterLink } from "@angular/router";

@Component({
    templateUrl: './bulk-sell.dialog.html',
    imports: [
        InventoryItemComponent,
        MoneysComponent,
        CommonModule,
        FormsModule,
        CeilPipe,
        RouterLink
    ],
    styleUrls: ['./bulk-sell.dialog.scss']
})
export class BulkSellDialog implements OnInit, OnDestroy {

  sellPrice: number|null = null;
  inventory: MyInventorySerializationGroup[];
  itemsToSell: { quantity: number, inventory: MyInventorySerializationGroup }[];
  user: MyAccountSerializationGroup;
  userSubscription = Subscription.EMPTY;
  alreadyForSale = 0;
  sellingSubscription = Subscription.EMPTY;

  constructor(
    @Inject(MAT_DIALOG_DATA) data,
    private dialogRef: MatDialogRef<BulkSellDialog>,
    private userData: UserDataService,
    private api: ApiService
  )
  {
    this.inventory = data.items;
    this.user = this.userData.user.getValue();

    this.itemsToSell = data.items
      .filter((i, index, self) => {
        return self.findIndex(i2 => this.hasSameGroup(i, i2)) === index;
      })
      .map(i => {
        return {
          quantity: data.items.filter(i2 => this.hasSameGroup(i, i2)).length,
          inventory: i,
        }
      })
    ;

    this.alreadyForSale = data.items.filter(i => !!i.sellPrice).length;
  }

  hasSameGroup = (i: MyInventorySerializationGroup, i2: MyInventorySerializationGroup) =>
    i2.item.name === i.item.name &&
    i2.sellPrice === i.sellPrice &&
    i2.enchantment?.name === i.enchantment?.name &&
    i2.spice?.name === i.spice?.name
  ;

  ngOnInit(): void
  {
    this.userSubscription = this.userData.user.subscribe({
      next: u => {
        this.user = u;
      }
    });
  }

  ngOnDestroy() {
    this.userSubscription.unsubscribe();
    this.sellingSubscription.unsubscribe();
  }

  doClose()
  {
    this.dialogRef.close();
  }

  doSell(sellPrice: number)
  {
    let itemsToUpdate;

    if(this.sellPrice > 0)
    {
      itemsToUpdate = this.inventory
        .filter(i => i.sellPrice !== this.sellPrice)
        .map(i => i.id)
      ;
    }
    else
    {
      itemsToUpdate = this.inventory
        .filter(i => !!i.sellPrice)
        .map(i => i.id)
      ;
    }

    if(itemsToUpdate.length === 0)
    {
      this.doClose();
      return;
    }

    const data = {
      price: sellPrice,
      items: itemsToUpdate
    };

    this.sellingSubscription = this.api.post('/inventory/sell', data).subscribe({
      next: () => {
        this.dialogRef.close({ newPrice: sellPrice });
      }
    });
  }

  public static open(matDialog: MatDialog, itemsToSell: MyInventorySerializationGroup[]): MatDialogRef<BulkSellDialog>
  {
    return matDialog.open(BulkSellDialog, {
      data: {
        items: itemsToSell
      }
    });
  }

}
