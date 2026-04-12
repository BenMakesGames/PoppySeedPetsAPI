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
import { BuiltInThemeSerializationGroup } from "../../../../model/built-in-theme.serialization-group";
import { ThemeService } from "../../../shared/service/theme.service";
import { ApiService } from "../../../shared/service/api.service";
import { Subscription } from "rxjs";
import { WeatherService } from "../../../shared/service/weather.service";
import { WeatherDataModel } from "../../../../model/weather.model";

@Component({
    templateUrl: './built-in.component.html',
    styleUrls: ['./built-in.component.scss'],
    standalone: false
})
export class BuiltInComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'The Painter - Built-in Themes' };

  useThemeSubscription = Subscription.EMPTY;
  weatherSubscription = Subscription.EMPTY;
  themes: BuiltInThemeSerializationGroup[] = [];

  constructor(
    private themeService: ThemeService,
    private api: ApiService,
    private weatherService: WeatherService
  ) { }

  ngOnInit(): void {
    this.themes = [ ...ThemeService.Themes ];

    this.weatherSubscription = this.weatherService.weather.subscribe({
      next: w => this.addWeatherThemes(w)
    });
  }

  ngOnDestroy() {
    this.useThemeSubscription.unsubscribe();
    this.weatherSubscription.unsubscribe();
  }

  private addWeatherThemes(weather: WeatherDataModel[]|null)
  {
    this.themes = [ ...ThemeService.Themes ];

    const today = weather?.find(w => new Date().toISOString().startsWith(w.date));
    const todaysHolidays = today ? today.holidays : [];

    for(let event of todaysHolidays)
    {
      if(event in ThemeService.EventThemes)
        this.themes.unshift(ThemeService.EventThemes[event]);
    }
  }

  doUse(theme: BuiltInThemeSerializationGroup)
  {
    this.useThemeSubscription = this.api.patch('/style/current', theme).subscribe({
      next: () => {
        this.themeService.setTheme(theme);
      }
    });
  }

}
