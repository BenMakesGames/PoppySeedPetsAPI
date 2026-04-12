/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, input, output } from '@angular/core';
import { RecyclingPointsComponent } from "../recycling-points/recycling-points.component";
import { RouterLink } from "@angular/router";
import { ItemOtherPropertiesIcons } from "../../../../model/item-other-properties-icons";

@Component({
    selector: 'app-item-tags',
    imports: [
        RecyclingPointsComponent,
        RouterLink
    ],
    templateUrl: './item-tags.component.html',
    styleUrl: './item-tags.component.scss'
})
export class ItemTagsComponent {
  item = input.required<ItemInterface>();
  onNav = output();

  readonly itemOtherPropertiesIcons = ItemOtherPropertiesIcons;

  doNav()
  {
    this.onNav.emit();
  }
}

interface ItemInterface {
  hat: any|null;
  greenhouseType: string;
  isFlammable: boolean;
  isFertilizer: boolean;
  isTreasure: boolean;
  recycleValue: number;
  itemGroups: { name: string }[];
}