import {Component, Input} from '@angular/core';
import { DatePipe } from "@angular/common";
import { PspTimeComponent } from "../psp-time/psp-time.component";

@Component({
    selector: 'app-date-and-time',
    template: `{{ dateString|date:'yyyy-MM-dd':'UTC' }}<small><app-psp-time [date]="dateString" /></small>`,
    imports: [
        DatePipe,
        PspTimeComponent
    ],
    styleUrls: ['./date-and-time.component.scss']
})
export class DateAndTimeComponent {

  @Input() dateString: string;

}
