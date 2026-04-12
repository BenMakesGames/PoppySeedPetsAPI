/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, Input, OnChanges, SimpleChanges} from '@angular/core';
import {ApiService} from "../../service/api.service";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {PetPublicProfileSerializationGroup} from "../../../../model/public-profile/pet-public-profile.serialization-group";
import {PetDailyLogsDialog} from "../../dialog/pet-daily-logs/pet-daily-logs.dialog";
import {Subscription} from "rxjs";
import { MatDialog } from "@angular/material/dialog";
import { CommonModule, DecimalPipe } from "@angular/common";
import { LoadingThrobberComponent } from "../loading-throbber/loading-throbber.component";

@Component({
    selector: 'app-pet-activity-log-calendar',
    templateUrl: './pet-activity-log-calendar.component.html',
    imports: [
        DecimalPipe,
        CommonModule,
        LoadingThrobberComponent
    ],
    styleUrls: ['./pet-activity-log-calendar.component.scss']
})
export class PetActivityLogCalendarComponent implements OnChanges {

  @Input() pet: PetPublicProfileSerializationGroup;

  days: CalendarDay[];

  currentYear: number;
  currentMonth: number;
  currentDay: number;

  emptyDays: any[];
  year: number;
  month: number;
  monthName: string;

  ajaxSubscription: Subscription;

  constructor(private api: ApiService, private matDialog: MatDialog) {
    const date = new Date();

    this.currentYear = date.getUTCFullYear();
    this.currentMonth = date.getUTCMonth() + 1;
    this.currentDay = date.getUTCDate();
  }

  ngOnChanges(changes: SimpleChanges)
  {
    if(changes.pet)
    {
      this.getCalendar();
    }
  }

  // pass "month" where 1 = January (not 0, like the normal stupid JS way)
  daysInMonth(year: number, month: number)
  {
    return new Date(year, month, 0).getDate();
  }

  doViewDaysLogs(day: number)
  {
    if(this.days[day - 1].quantity === 0) return;

    PetDailyLogsDialog.open(this.matDialog, this.pet, new Date(Date.UTC(this.year, this.month - 1, day)));
  }

  doNextMonth()
  {
    if(this.ajaxSubscription)
      this.ajaxSubscription.unsubscribe();

    this.month++;

    if(this.month === 13)
    {
      this.month = 1;
      this.year++;
    }

    this.computeMonthName();
    this.computeFirstDayOfMonth();

    this.getCalendar();
  }

  doPreviousMonth()
  {
    if(this.ajaxSubscription)
      this.ajaxSubscription.unsubscribe();

    this.month--;

    if(this.month === 0)
    {
      this.year--;
      this.month = 12;
    }

    this.computeMonthName();
    this.computeFirstDayOfMonth();

    this.getCalendar();
  }

  private computeFirstDayOfMonth()
  {
    const dayOfWeek = (new Date(this.year, this.month - 1, 1)).getUTCDay();

    if(dayOfWeek === 0)
      this.emptyDays = [ null, null, null, null, null, null ];
    else
    {
      this.emptyDays = [];

      for(let i = 1; i < dayOfWeek; i++)
        this.emptyDays.push(null);
    }
  }

  private computeMonthName()
  {
    this.monthName = [
      'January',
      'February',
      'March',
      'April',
      'May',
      'June',
      'July',
      'August',
      'September',
      'October',
      'November',
      'December'
    ][this.month - 1];
  }

  private getCalendar()
  {
    if(this.ajaxSubscription && !this.ajaxSubscription.closed) return;

    let url = '/pet/' + this.pet.id + '/logs/calendar';

    if(this.year && this.month)
      url += '/' + this.year + '/' + this.month;

    this.ajaxSubscription = this.api.get<CalendarResponse>(url).subscribe({
      next: (r: ApiResponseModel<CalendarResponse>) => {
        this.days = [];

        this.year = r.data.year;
        this.month = r.data.month;

        this.computeMonthName();
        this.computeFirstDayOfMonth();

        const daysInMonth = this.daysInMonth(this.year, this.month);

        for(let day = 1; day <= daysInMonth; day++)
        {
          this.days.push({
            quantity: 0,
            averageInterestingness: 0,
          });
        }

        r.data.calendar.forEach(c => {
          const dateParts = c.yearMonthDay.split('-');
          const day = Number(dateParts[2]);

          this.days[day - 1] = {
            quantity: c.quantity,
            averageInterestingness: c.averageInterestingness
          };
        });
      }
    })
  }
}

interface CalendarResponse {
  year: number;
  month: number;
  calendar: CalendarResponseDay[];
}

interface CalendarResponseDay {
  quantity: number;
  averageInterestingness: number;
  yearMonthDay: string;
}

interface CalendarDay {
  quantity: number;
  averageInterestingness: number;
}
