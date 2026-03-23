import { Component, input } from '@angular/core';
import { DatePipe } from "@angular/common";

@Component({
    selector: 'app-date-only',
    template: `{{ dateString()|date:'yyyy-MM-dd':'UTC' }}`,
    imports: [
        DatePipe
    ],
    styleUrls: ['./date-only.component.scss']
})
export class DateOnlyComponent {

  dateString = input.required<string>();

}
