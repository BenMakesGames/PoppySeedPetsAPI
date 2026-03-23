import { Component, computed, input } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
    selector: 'app-need-bar',
    imports: [CommonModule],
    templateUrl: './need-bar.component.html',
    styleUrl: './need-bar.component.scss'
})
export class NeedBarComponent {
  label = input.required<string>();
  value = input.required<number>();

  color = computed(() => this.value() < 0 ? 'rgb(var(--color-warning))' : 'rgb(var(--color-gain))');
  leftPercent = computed(() => this.value() < 0 ? Math.max(0, 0.5 + (this.value() / 2)) : 0.5);
  widthPercent = computed(() => Math.min(0.5, Math.abs(this.value() / 2)));
}
