import { Component, computed, input } from '@angular/core';

@Component({
    selector: 'app-progress-bar',
    templateUrl: './progress-bar.component.html',
    imports: [],
    styleUrls: ['./progress-bar.component.scss']
})
export class ProgressBarComponent {

  percent = input<number>(0);
  nextPercent = input<number|null>(null);
  label = input<string>('');

  humanReadablePercent = computed(() => `${Math.floor(this.percent() * 100)}%`);
}
