import {Component, Input} from '@angular/core';
import { CommonModule } from "@angular/common";

@Component({
  selector: 'app-form-field',
  template: `
    <label [for]="labels">
      {{ label }}
      <span>({{ value?.length ?? 0 }}<ng-container *ngIf="maxLength">/{{ maxLength }}</ng-container>)</span>
    </label>
    <ng-content></ng-content>
  `,
  imports: [
      CommonModule
  ],
  styleUrls: ['./form-field.component.scss']
})
export class FormFieldComponent {

  @Input() label: string;
  @Input() labels: string;
  @Input() value: string|null;
  @Input() maxLength: number;
}
