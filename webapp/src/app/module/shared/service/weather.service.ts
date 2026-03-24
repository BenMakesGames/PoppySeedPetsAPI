import {Injectable} from '@angular/core';
import { BehaviorSubject, Subscription, timer } from "rxjs";
import { WeatherDataModel } from "../../../model/weather.model";
import { ApiService } from "./api.service";

@Injectable({
  providedIn: 'root'
})
export class WeatherService {
  weather = new BehaviorSubject<WeatherDataModel[]|null>(null);

  #weatherAjax = Subscription.EMPTY;
  #lastUpdated: Date|null = null;

  constructor(private readonly apiService: ApiService) {
    // every 1 second, check if it's a new day; if so, update the weather
    timer(0, 1000).subscribe({
      next: () => {
        if(this.#weatherAjax.closed)
        {
          if(this.weather.getValue() === null || this.#lastUpdated === null || new Date().getUTCDay() > this.#lastUpdated.getUTCDay())
          {
            this.#lastUpdated = new Date();
            this.updateWeather();
          }
        }
      }
    });

  }

  updateWeather()
  {
    this.#weatherAjax.unsubscribe();
    this.#weatherAjax = this.apiService.get<{ forecast: WeatherDataModel[] }>('/weather').subscribe({
      next: r => {
        if(r.data?.forecast?.length > 0)
          this.weather.next(r.data.forecast);
        else
          this.#lastUpdated = null;
      },
      error: () => {
        this.#lastUpdated = null;
      }
    });
  }
}
