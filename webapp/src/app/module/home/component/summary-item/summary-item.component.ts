import { Component, Input } from '@angular/core';

@Component({
  selector: '.app-summary-item',
  templateUrl: './summary-item.component.html',
  styleUrls: ['./summary-item.component.scss']
})
export class SummaryItemComponent {

  @Input() summaryItem;

  constructor() { }

}
