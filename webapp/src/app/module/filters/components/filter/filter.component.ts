import { Component, EventEmitter, Input, Output } from '@angular/core';

@Component({
    selector: 'app-filter',
    templateUrl: './filter.component.html',
    styleUrls: ['./filter.component.scss']
})
export class FilterComponent {
  @Input() label: string;
  @Input() value: string;

  @Output() clear = new EventEmitter<void>();

  public doClear()
  {
    this.clear.emit();
  }
}
