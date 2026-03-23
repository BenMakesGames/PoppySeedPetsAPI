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
