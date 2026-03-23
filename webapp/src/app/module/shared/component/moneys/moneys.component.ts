import { Component, input } from '@angular/core';
import { DecimalPipe } from "@angular/common";

@Component({
    selector: 'app-moneys',
    imports: [
        DecimalPipe
    ],
    template: `{{ amount()|number:'1.0-2' }}<i class="moneys" title="moneys" role="img"></i>`
})
export class MoneysComponent {
  amount = input.required<number>();
}
