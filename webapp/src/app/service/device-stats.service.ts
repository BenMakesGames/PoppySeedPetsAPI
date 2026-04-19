/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
