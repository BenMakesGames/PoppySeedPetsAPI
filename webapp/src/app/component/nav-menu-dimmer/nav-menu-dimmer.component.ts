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
import { Subscription } from "rxjs";
import { WeatherSky } from "../../model/weather.model";

@Component({
    selector: 'app-nav-menu-dimmer',
    templateUrl: './nav-menu-dimmer.component.html',
    styleUrls: ['./nav-menu-dimmer.component.scss'],
    standalone: false
})
export class NavMenuDimmerComponent implements OnInit, OnDestroy {

  weatherSubscription = Subscription.EMPTY;

  snow = false;

  constructor(private weatherService: WeatherService) { }

  ngOnInit() {
    this.weatherSubscription = this.weatherService.weather.subscribe({
      next: weather => {
        const today = weather?.find(w => new Date().toISOString().startsWith(w.date));

        this.snow = today?.sky === WeatherSky.Snowy
          || today?.holidays.includes('Snow Moon') === true;
      }
    });
  }

  ngOnDestroy() {
    this.weatherSubscription.unsubscribe();
  }
}
