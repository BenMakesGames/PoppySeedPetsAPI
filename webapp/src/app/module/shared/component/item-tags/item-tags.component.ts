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