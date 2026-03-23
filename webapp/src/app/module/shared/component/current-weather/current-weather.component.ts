import { Component, computed, input } from '@angular/core';
import { CommonModule } from "@angular/common";
import { WeatherSky } from "../../../../model/weather.model";

@Component({
    selector: 'app-current-weather',
    templateUrl: './current-weather.component.html',
    imports: [
        CommonModule
    ],
    styleUrls: ['./current-weather.component.scss']
})
export class CurrentWeatherComponent {

  sky = input.required<WeatherSky>();
  weatherIcon = computed<string|null>(() => CurrentWeatherComponent.determineWeatherIcon(this.sky()));

  private static determineWeatherIcon(sky: WeatherSky): string|null
  {
    switch(sky)
    {
      case WeatherSky.Clear: return 'sunny';
      case WeatherSky.Cloudy: return 'cloudy';
      case WeatherSky.Rainy: return 'rainy';
      case WeatherSky.Snowy: return 'snowy';
      case WeatherSky.Stormy: return 'stormy';
    }

    return null;
  }
}
