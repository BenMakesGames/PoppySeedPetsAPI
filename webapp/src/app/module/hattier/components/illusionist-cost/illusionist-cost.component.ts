import { Component, Input } from '@angular/core';

@Component({
    selector: 'app-illusionist-cost',
    templateUrl: './illusionist-cost.component.html',
    styleUrls: ['./illusionist-cost.component.scss'],
    standalone: false
})
export class IllusionistCostComponent {
  @Input() amount: number;
  @Input() currency: string;
}
