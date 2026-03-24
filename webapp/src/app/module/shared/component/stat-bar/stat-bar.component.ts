import {Component, Input, OnChanges} from '@angular/core';
import { CommonModule } from "@angular/common";

@Component({
    imports: [
        CommonModule
    ],
    selector: 'app-stat-bar',
    templateUrl: './stat-bar.component.html',
    styleUrls: ['./stat-bar.component.scss']
})
export class StatBarComponent implements OnChanges {

  bars: number[] = [];

  @Input() value: number;
  @Input() shineOffset: number;

  constructor() { }

  ngOnChanges(): void
  {
    const v = Math.max(0, Math.min(20, this.value));

    this.bars = [];

    for(let i = 0; i < v; i++)
      this.bars.push((150 + i * 10) % 360);
  }
}
