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
