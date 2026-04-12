/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, Input } from '@angular/core';
import { LocationEnum } from "../../../../model/location.enum";
import { MyInventoryItemSerializationGroup } from "../../../../model/my-inventory/my-inventory-item.serialization-group";
import { ApiService } from "../../../shared/service/api.service";
import { SummaryItemComponent } from "../summary-item/summary-item.component";
import { CommonModule } from "@angular/common";

@Component({
    selector: 'app-summary-location',
    templateUrl: './summary-location.component.html',
    styleUrls: ['./summary-location.component.scss'],
    imports: [
        SummaryItemComponent,
        CommonModule,
    ]
})
export class SummaryLocationComponent {

  @Input() location: LocationEnum;

  items: SummaryItem[]|undefined;

  constructor(private api: ApiService) { }

  doExpand()
  {
    this.api.get<SummaryItem[]>('/inventory/summary/' + this.location).subscribe({
      next: r => {
        this.items = r.data.sort((a, b) => a.item.name.localeCompare(b.item.name));
      }
    })

  }

}

interface SummaryItem
{
  quantity: number;
  location: LocationEnum;
  item: MyInventoryItemSerializationGroup;
}