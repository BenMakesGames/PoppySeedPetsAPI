import {Component, Input, OnChanges} from '@angular/core';
import {QueryStringService} from "../../../../service/query-string.service";
import { RouterLink } from "@angular/router";
import { CommonModule } from "@angular/common";

@Component({
    selector: 'app-url-paginator',
    templateUrl: './url-paginator.component.html',
    imports: [
        RouterLink,
        CommonModule,
    ],
    styleUrls: ['./url-paginator.component.scss']
})
export class UrlPaginatorComponent implements OnChanges {

  @Input({ required: true }) path: string;
  @Input({ required: true }) page: number;
  @Input({ required: true }) pageCount: number;
  @Input() params: any;

  pages: (number|null)[] = [];
  queryParams: any[] = [];

  ngOnChanges(changes)
  {
    if(changes.page || changes.pageCount || changes.params)
    {
      this.pages = [];
      this.queryParams = [];

      for(let i = 0; i < this.pageCount; i++)
      {
        if(i < 3 || i >= this.pageCount - 3 || Math.abs(i - this.page) <= 2)
        {
          this.pages.push(i);
          this.queryParams.push(
            QueryStringService.convertToAngularParams({
              ...this.params,
              page: i
            })
          );
        }
        else if(this.pages[this.pages.length - 1] !== null)
        {
          this.pages.push(null);
          this.queryParams.push(null);
        }
      }
    }
  }
}
