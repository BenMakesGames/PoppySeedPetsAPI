/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, OnDestroy, OnInit } from '@angular/core';
import { Subscription } from "rxjs";
import { ApiService } from "../../../shared/service/api.service";
import { DescribeCalendarDayDialog } from "../../dialog/describe-calendar-day/describe-calendar-day.dialog";
import { MatDialog } from "@angular/material/dialog";

@Component({
    templateUrl: './event-calendar.component.html',
    styleUrl: './event-calendar.component.scss',
    standalone: false
})
export class EventCalendarComponent implements OnInit, OnDestroy {
  calendarAjax = Subscription.EMPTY;

  calendar: Calendar|null = null;

  constructor(
    private readonly apiService: ApiService,
    private readonly matDialog: MatDialog
  )
  {
  }

  ngOnInit() {
    this.calendarAjax = this.apiService.get<Calendar>('/plaza/eventCalendar').subscribe(r => {
      this.calendar = r.data;
    });
  }

  ngOnDestroy() {
    this.calendarAjax.unsubscribe();
  }

  doDescribeDay(day: CalendarDay)
  {
    DescribeCalendarDayDialog.open(this.matDialog, day.date, day.holidays);
  }
}

interface Calendar
{
  today: string;
  years: CalendarYear[];
}

interface CalendarYear
{
  year: number;
  months: CalendarMonth[];
}

interface CalendarMonth
{
  month: string;
  days: CalendarDay[];
}

interface CalendarDay
{
  dayOfWeek: number;
  date: string;
  holidays: string[];
}