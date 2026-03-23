import { Component, Input, OnChanges } from '@angular/core';
import { CommonModule } from "@angular/common";

@Component({
    selector: 'app-heart-meter',
    templateUrl: './heart-meter.component.html',
    imports: [
        CommonModule
    ],
    styleUrls: ['./heart-meter.component.scss']
})
export class HeartMeterComponent implements OnChanges {

  @Input() commitment = 0;

  meter = [ 'e', 'e', 'e', 'e', 'e' ];

  ngOnChanges() {
    for(let i = 0; i < 5; i++)
    {
      if(i + 1 <= this.commitment)
        this.meter[i] = 'f';
      else if(i < this.commitment)
        this.meter[i] = 'h';
      else
        this.meter[i] = 'e';
    }
  }
}
