/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
