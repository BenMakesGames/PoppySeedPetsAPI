/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, EventEmitter, Input, OnChanges, Output, SimpleChanges } from '@angular/core';
import { FormsModule } from "@angular/forms";
import { MatDialog } from "@angular/material/dialog";
import { PetSearchDialog } from "../../dialog/pet-search/pet-search.dialog";
import { CreatePetSearchModel, PetSearchModel } from "../../../../model/search/pet-search-model";
import { FilterAreaComponent } from "../../../filters/components/filter-area/filter-area.component";

@Component({
  imports: [
    FormsModule,
    FilterAreaComponent,
  ],
  selector: 'app-pet-search',
  templateUrl: './pet-search.component.html',
  styleUrls: ['./pet-search.component.scss']
})
export class PetSearchComponent implements OnChanges {

  @Input() filters: PetSearchModel = CreatePetSearchModel();
  @Output() filtersChange = new EventEmitter<PetSearchModel>();

  name: string;

  constructor(private matDialog: MatDialog) { }

  ngOnChanges(changes: SimpleChanges)
  {
    if('filters' in changes)
    {
      this.name = this.filters.name;
    }
  }

  doSearch(filters: PetSearchModel)
  {
    this.filters = filters;
    this.filtersChange.emit(filters);
  }

  doMore()
  {
    PetSearchDialog.open(this.matDialog, this.filters).afterClosed().subscribe({
      next: (r: PetSearchModel) => {
        if(r)
        {
          this.filtersChange.emit(r);
        }
      }
    });
  }
}
