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

@Component({
  selector: 'app-filter-area',
  templateUrl: './filter-area.component.html',
  styleUrls: ['./filter-area.component.scss'],
  imports: [
    FormsModule
  ]
})
export class FilterAreaComponent implements OnChanges
{
  @Input() textExactMatchName: string|null = null;
  @Input() textName: string;

  @Input() filters: any = {};

  @Output() search = new EventEmitter<any>();
  @Output() more = new EventEmitter<void>();

  filterCount = 0;

  ngOnChanges(changes: SimpleChanges)
  {
    this.filterCount = 0;

    const keys = Object.keys(this.filters).filter(k => this.filters.hasOwnProperty(k));

    for(const key of keys)
    {
      if(this.filters[key] !== null && this.filters[key] !== undefined)
      {
        if(this.filters[key].constructor.name == 'Array')
        {
          if(this.filters[key].length > 0)
            this.filterCount++;
        }
        else if(key !== this.textName && key !== this.textExactMatchName)
          this.filterCount++;
      }
    }
  }

  doToggleExactMatchName()
  {
    this.filters[this.textExactMatchName] = !this.filters[this.textExactMatchName];
  }

  doMore()
  {
    this.more.emit();
  }

  doSearch()
  {
    this.search.emit(this.filters);
  }
}
