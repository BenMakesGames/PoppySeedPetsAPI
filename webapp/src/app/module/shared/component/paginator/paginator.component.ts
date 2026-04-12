/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, EventEmitter, Input, OnChanges, Output} from '@angular/core';
import { CommonModule } from "@angular/common";

/**
 * @deprecated Use UrlPaginatorComponent, instead
 */
@Component({
    imports: [
        CommonModule,
    ],
    selector: 'app-paginator',
    templateUrl: './paginator.component.html',
    styleUrls: ['./paginator.component.scss']
})
export class PaginatorComponent implements OnChanges {

  @Input() pageCount: number;

  // for [(page)]...
  @Input() page: number;
  @Output() pageChange = new EventEmitter<number>();

  // generic change event
  @Output() change = new EventEmitter<void>();

  public pages: (number|null)[] = [];

  constructor() { }

  doSelectPage(page: number)
  {
    if(page === null || page === this.page) return;

    this.page = page;
    this.pageChange.emit(page);
    this.change.emit();
  }

  ngOnChanges(changes)
  {
    if(changes.page || changes.pageCount)
    {
      this.pages = [];

      for(let i = 0; i < this.pageCount; i++)
      {
        if(i < 3 || i >= this.pageCount - 3 || Math.abs(i - this.page) <= 2)
          this.pages.push(i);
        else if(this.pages[this.pages.length - 1] !== null)
          this.pages.push(null);
      }
    }
  }
}
