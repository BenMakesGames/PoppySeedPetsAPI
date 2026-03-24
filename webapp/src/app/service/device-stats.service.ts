import { Injectable } from '@angular/core';
import {UserDataService} from "./user-data.service";
import {ApiService} from "../module/shared/service/api.service";

@Injectable({
  providedIn: 'root'
})
export class DeviceStatsService {

  constructor(private userData: UserDataService, private api: ApiService) {
    const interval = window.setInterval(() => {
      if(this.userData.user.getValue() === null || this.userData.user.getValue().id === null)
        return;

      clearInterval(interval);

      this.logStats();
    }, 15000);
  }

  private logStats()
  {
    this.api.put('/deviceStats', {
      'userAgent': navigator.userAgent,
      'language': navigator.language,
      'touchPoints': navigator.maxTouchPoints,
      'windowWidth': window.innerWidth,
      'screenWidth': screen.availWidth,
    }).subscribe();
  }
}
