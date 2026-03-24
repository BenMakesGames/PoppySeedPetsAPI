import { Component, OnDestroy, OnInit } from '@angular/core';
import { WeatherDataModel } from "../../../../model/weather.model";
import { UserDataService } from "../../../../service/user-data.service";
import { WeatherService } from "../../../shared/service/weather.service";
import { Subscription } from "rxjs";

@Component({
    selector: 'app-weather-forecast',
    templateUrl: './weather-forecast.component.html',
    styleUrls: ['./weather-forecast.component.scss'],
    standalone: false
})
export class WeatherForecastComponent implements OnInit, OnDestroy {

  forecast: WeatherDataModel[] = [];
  allowanceDayOfWeek: string;
  weatherSubscription = Subscription.EMPTY;
  today: WeatherDataModel|null = null;
  currentDate = new Date();

  constructor(
    private userData: UserDataService, private weatherService: WeatherService
  ) {

  }

  ngOnInit(): void
  {
    this.weatherSubscription = this.weatherService.weather.subscribe({
      next: w => {
        this.today = w?.find(w => new Date().toISOString().startsWith(w.date)) || null;
        this.forecast = w?.filter(w => !(new Date().toISOString().startsWith(w.date))) || [];
      }
    });

    const user = this.userData.user.getValue();
    const lastAllowanceCollected = new Date(user.lastAllowanceCollected);
    this.allowanceDayOfWeek = [
      'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'
    ][lastAllowanceCollected.getUTCDay()];
  }

  ngOnDestroy() {
    this.weatherSubscription.unsubscribe();
  }
}
