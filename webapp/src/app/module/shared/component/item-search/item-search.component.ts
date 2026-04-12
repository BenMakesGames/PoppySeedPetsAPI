/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
