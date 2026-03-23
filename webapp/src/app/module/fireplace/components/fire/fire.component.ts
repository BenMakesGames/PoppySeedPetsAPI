import { Component, input } from '@angular/core';

@Component({
    selector: 'app-fire',
    templateUrl: './fire.component.html',
    styleUrl: './fire.component.scss',
    standalone: false
})
export class FireComponent {
  strength = input.required<number>();
  protected readonly Math = Math;
}
