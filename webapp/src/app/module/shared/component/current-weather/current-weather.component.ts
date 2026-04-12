/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
