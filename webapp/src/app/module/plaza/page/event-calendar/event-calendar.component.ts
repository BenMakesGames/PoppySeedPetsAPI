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