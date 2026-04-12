/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, computed, input } from '@angular/core';
import {UserDataService} from "../../../../service/user-data.service";
import {MyAccountSerializationGroup} from "../../../../model/my-account/my-account.serialization-group";
import {EstimatedWidthPipe} from "../../pipe/estimated-width.pipe";
import { RecyclingPointsComponent } from "../recycling-points/recycling-points.component";
import { ItemNameWithBonusComponent } from "../item-name-with-bonus/item-name-with-bonus.component";
import { HasUnlockedFeaturePipe } from "../../pipe/has-unlocked-feature.pipe";
import { CommonModule } from "@angular/common";

@Component({
    selector: 'app-inventory-item',
    templateUrl: './inventory-item.component.html',
    imports: [
        RecyclingPointsComponent,
        ItemNameWithBonusComponent,
        HasUnlockedFeaturePipe,
        CommonModule
    ],
    styleUrls: ['./inventory-item.component.scss']
})
export class InventoryItemComponent {

  inventory = input.required<InventoryItem>();
  lockedToOwner = input<boolean>(false);
  showRecycleValue = input<boolean>(false);
  museumPoints = input<number|null>(null);
  subtitle = input<string|null>(null);

  nameIsLong = computed(() => {
    const inv = this.inventory();
    let nameParts = [ inv.item.name ];

    if(inv.enchantment)
      nameParts.push(inv.enchantment.name);

    if(inv.spice)
      nameParts.push(inv.spice.name);

    return (new EstimatedWidthPipe()).transform(nameParts.join(' ')) > 20;
  });

  user: MyAccountSerializationGroup;

  constructor(private userDataService: UserDataService) {
    this.user = this.userDataService.user.getValue();
  }
}

interface InventoryItem
{
  enchantment?: any;
  spice?: any;
  item: { image: string, name: string, recycleValue?: number };
  illusion: { image: string, name: string }|null;
  lockedToOwner?: boolean;
  holder?: any;
  wearer?: any;
  sellPrice?: number;
}