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