import { Component, Input } from '@angular/core';
import { DecimalPipe } from "@angular/common";

@Component({
    selector: 'app-recycling-points',
    imports: [
        DecimalPipe
    ],
    template: `{{ amount|number:'1.0-2' }}<i class="recycle" title="recycling points" role="img"></i>`
})
export class RecyclingPointsComponent {
  @Input() amount = 0;
}
