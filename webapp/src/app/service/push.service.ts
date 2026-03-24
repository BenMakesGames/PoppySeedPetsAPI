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
