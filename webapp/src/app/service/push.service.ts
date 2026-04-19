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
import {SwPush} from "@angular/service-worker";
import {environment} from "../../environments/environment";
import {ApiService} from "../module/shared/service/api.service";
import {take} from "rxjs/operators";

@Injectable({
  providedIn: 'root'
})
export class PushService {

  private currentDeviceSubscriptionEndpoint: string|null;

  constructor(private swPush: SwPush, private api: ApiService)
  {
    this.swPush.subscription.subscribe((s: PushSubscription|null) => {
      if(s === null)
        this.currentDeviceSubscriptionEndpoint = null;
      else
        this.currentDeviceSubscriptionEndpoint = s.endpoint;
    });
  }

  async unsubscribeThisDevice(): Promise<void>
  {
    return new Promise(resolve => {
      this.swPush.subscription.pipe(take(1)).subscribe((s: PushSubscription) => {
        s.unsubscribe().then(() => {
          this.currentDeviceSubscriptionEndpoint = null;
          resolve();
        });
      });
    });
  }

  async subscribeThisDevice(): Promise<boolean>
  {
    if(this.currentDeviceSubscriptionEndpoint)
      return false;

    let pushSubscription = await this.swPush.requestSubscription({
      serverPublicKey: environment.vapidPublicKey
    });

    if(pushSubscription === null)
      return false;

    return new Promise(resolve => {
      this.api.post('/notification/pushSubscription', pushSubscription.toJSON())
        .subscribe({
          next: () => {
            resolve(true);
          },
          error: () => {
            resolve(false);
          }
        })
      ;
    });
  }

  isSupported(): boolean
  {
    return this.swPush.isEnabled;
  }

  getCurrentDeviceSubscriptionEndpoint(): string|null
  {
    return this.currentDeviceSubscriptionEndpoint;
  }
}
