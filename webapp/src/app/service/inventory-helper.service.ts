import { Injectable } from '@angular/core';
import { Observable, of } from "rxjs";
import { MyInventorySerializationGroup } from "../model/my-inventory/my-inventory.serialization-group";
import { ApiService } from "../module/shared/service/api.service";
import { map } from "rxjs/operators";
import { MessagesService } from "./messages.service";
import { InventoryModeEnum } from "../model/inventory-mode.enum";

@Injectable({
  providedIn: 'root'
})
export class InventoryHelperService {

  constructor(private api: ApiService, private messageService: MessagesService) { }

  public findAndUpdateInventorySellPrice(inventory: MyInventorySerializationGroup[], inventoryId: number, newSellPrice: number|null): Observable<void>
  {
    const updatedInventory = inventory.find(i => i.id === inventoryId);

    if(!updatedInventory)
    {
      this.messageService.addGenericMessage('Oops! That item no longer exists! (Maybe a pet used it up??)');
      return of();
    }

    return this.updateInventorySellPrice(updatedInventory, newSellPrice);
  }

  public updateInventorySellPrice(inventory: MyInventorySerializationGroup, newSellPrice: number|null): Observable<void>
  {
    if(newSellPrice === inventory.sellPrice)
      return of();

    const data = {
      price: newSellPrice,
      items: [ inventory.id ]
    };

    return this.api.post<number|null>('/inventory/sell', data).pipe(
      map(_ => {
        inventory.sellPrice = newSellPrice;
      })
    );
  }

  public multiSelectInventory(inventory: MyInventorySerializationGroup[], itemId: number, selected: boolean, mode: InventoryModeEnum)
  {
    inventory.forEach(i => {
      if(mode === InventoryModeEnum.Selling && i.lockedToOwner)
        return;

      if(i.item.id === itemId)
        i.selected = selected;
    });
  }
}
