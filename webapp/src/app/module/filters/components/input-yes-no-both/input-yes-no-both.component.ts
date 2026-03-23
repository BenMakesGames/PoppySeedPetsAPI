import { Component, EventEmitter, Input, Output } from '@angular/core';
import { FormsModule } from "@angular/forms";

@Component({
  selector: 'app-input-yes-no-both',
  templateUrl: './input-yes-no-both.component.html',
  imports: [
    FormsModule
  ],
  styleUrls: [ './input-yes-no-both.component.scss' ]
})
export class InputYesNoBothComponent {
  @Input() nullLabel = 'Either';

  @Input() value: boolean|null = null;
  @Output() valueChange = new EventEmitter<boolean|null>();

  id = 'yesNoBoth-' + Math.random().toString(36).substring(2);
}
