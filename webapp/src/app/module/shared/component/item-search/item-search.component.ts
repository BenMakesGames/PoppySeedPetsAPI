import {
  Component,
  EventEmitter,
  Input, OnChanges,
  Output, SimpleChanges,
} from '@angular/core';
import {CreateItemSearchModel, ItemSearchModel} from "../../../../model/search/item-search.model";
import { CommonModule } from "@angular/common";
import { FormsModule } from "@angular/forms";
import { MatDialog } from "@angular/material/dialog";
import { ItemSearchDialog } from "../../dialog/item-search/item-search.dialog";
import { FilterAreaComponent } from "../../../filters/components/filter-area/filter-area.component";

@Component({
  selector: 'app-item-search',
  templateUrl: './item-search.component.html',
  imports: [
    CommonModule,
    FormsModule,
    FilterAreaComponent,
  ],
  styleUrls: ['./item-search.component.scss']
})
export class ItemSearchComponent implements OnChanges
{
  @Input() filters: ItemSearchModel = CreateItemSearchModel();
  @Output() filtersChange = new EventEmitter<ItemSearchModel>();

  name: string;

  constructor(private matDialog: MatDialog) { }

  ngOnChanges(changes: SimpleChanges)
  {
    if('filters' in changes)
    {
      this.name = this.filters.name;
    }
  }

  doSearch(filters: ItemSearchModel)
  {
    this.filtersChange.emit(filters);
  }

  doMore()
  {
    ItemSearchDialog.open(this.matDialog, this.filters).afterClosed().subscribe({
      next: (r: ItemSearchModel) => {
        if(r)
        {
          this.filtersChange.emit(r);
        }
      }
    });
  }
}
