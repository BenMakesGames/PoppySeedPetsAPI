import { Component, OnDestroy, OnInit } from '@angular/core';
import { WeatherService } from "../../module/shared/service/weather.service";
import { Subscription, timer } from "rxjs";
import { WeatherDataModel, WeatherSky } from "../../model/weather.model";

@Component({
    selector: 'app-weather-report',
    templateUrl: './weather-report.component.html',
    styleUrls: ['./weather-report.component.scss'],
    standalone: false
})
export class WeatherReportComponent implements OnInit, OnDestroy {

  weatherSubscription = Subscription.EMPTY;
  weather: WeatherDataModel|null = null;
  rainfallDescription = 'none';
  now = new Date();
  clockSubscription = Subscription.EMPTY;

  constructor(private weatherService: WeatherService) { }

  ngOnInit(): void
  {
    this.clockSubscription = timer(1000, 1000).subscribe({
      next: () => {
        this.now = new Date();
      }
    });

    this.weatherSubscription = this.weatherService.weather.subscribe({
      next: w => {
        this.weather = w?.find(weather => new Date().toISOString().startsWith(weather.date));

        this.rainfallDescription = this.weather
          ? WeatherReportComponent.describeSky(this.weather.sky)
          : ''
        ;
      }
    });
  }

  ngOnDestroy()
  {
    this.weatherSubscription.unsubscribe();
    this.clockSubscription.unsubscribe();
  }

  static describeSky(sky: WeatherSky): string
  {
    switch(sky)
    {
      case WeatherSky.Clear:
        return 'clear skies';
      case WeatherSky.Cloudy:
        return 'cloudy';
      case WeatherSky.Rainy:
        return 'rain';
      case WeatherSky.Snowy:
        return 'snow';
      case WeatherSky.Stormy:
        return 'storm';
    }
  }
}
