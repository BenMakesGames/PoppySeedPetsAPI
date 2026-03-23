import { Component, computed, input } from '@angular/core';

@Component({
  standalone: true,
  selector: 'app-vague-change',
  template: '<span [class.gain]="direction() > 0" [class.loss]="direction() < 0">{{ change() }} {{ label() }}</span>',
  styleUrls: ['./vague-change.component.scss']
})
export class VagueChangeComponent {

  change = input.required<string>();
  label = input.required<string>();

  direction = computed(() => this.change().startsWith('+') ? 1 : -1);

}
