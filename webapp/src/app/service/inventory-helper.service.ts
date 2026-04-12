/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
