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
