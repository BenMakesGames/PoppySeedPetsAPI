import { Component, input, OnDestroy, OnInit } from '@angular/core';
import { Subscription } from "rxjs";
import { WeatherService } from "../../../shared/service/weather.service";
import { WeatherDataModel } from "../../../../model/weather.model";

@Component({
    selector: 'app-hello-dialog',
    templateUrl: './hello-dialog.component.html',
    styleUrl: './hello-dialog.component.scss',
    standalone: false
})
export class HelloDialogComponent implements OnInit, OnDestroy {

  weatherSubscription = Subscription.EMPTY;

  holidayBoxes = input.required<string[]>();
  firstLine = input.required<string>();

  weatherDialog = '';

  constructor(private weatherService: WeatherService)
  {
  }

  ngOnInit(): void
  {
    this.weatherSubscription = this.weatherService.weather.subscribe({
      next: weather => {
        this.resetHelloDialog(weather);
      }
    });
  }

  ngOnDestroy() {
    this.weatherSubscription.unsubscribe();
  }

  private resetHelloDialog(weather: WeatherDataModel[]|null)
  {
    const today = weather?.find(w => new Date().toISOString().startsWith(w.date));
    const todaysHolidays = today ? today.holidays : [];

    this.weatherDialog = '';

    if(todaysHolidays.length > 0)
    {
      this.weatherDialog += 'Are you having a fun ' + todaysHolidays.map(h => h.replace(/^the /i, '')).listNice(', ', ', _and_ ') + '?';
    }

    const holidays = weather
        ?.reduce((list, f) => {
          list.push(...f.holidays);
          return list;
        }, [])
        .filter((value, index, self) => self.indexOf(value) === index) // filter out duplicates
        .filter(v => todaysHolidays.indexOf(v) < 0) // filter out copies of today's holiday
      ?? []
    ;

    if(holidays.length > 0)
    {
      if(todaysHolidays.length > 0)
        this.weatherDialog += '\n\n' + holidays.listNice() + ' ' + (holidays.length === 1 ? 'is' : 'are') + ' coming up, too! Busy week!';
      else
        this.weatherDialog += '\n\nAre you excited for ' + holidays.listNice(', ', ', _and_ ') + '?';

      return;
    }
  }

}
