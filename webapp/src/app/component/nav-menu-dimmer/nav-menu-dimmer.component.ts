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
