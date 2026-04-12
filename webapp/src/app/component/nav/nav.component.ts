/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, ElementRef, OnDestroy, OnInit, ViewChild} from '@angular/core';
import {Subscription} from "rxjs";
import {Router} from "@angular/router";
import {NavService} from "../../service/nav.service";
import {UserDataService} from "../../service/user-data.service";
import {ThemeService} from "../../module/shared/service/theme.service";
import { WeatherDataModel } from "../../model/weather.model";
import { WeatherService } from "../../module/shared/service/weather.service";

@Component({
    selector: 'app-nav',
    templateUrl: './nav.component.html',
    styleUrls: ['./nav.component.scss'],
    standalone: false
})
export class NavComponent implements OnInit, OnDestroy {

  @ViewChild('nav', { 'static': true }) private nav: ElementRef;

  private weatherSubscription = Subscription.EMPTY;
  private userSubscription = Subscription.EMPTY;
  private routerSubscription = Subscription.EMPTY;
  private navServiceSubscription = Subscription.EMPTY;
  private disableShadowSubscription = Subscription.EMPTY;
  private hamburgerSubscription = Subscription.EMPTY;

  public weather: WeatherDataModel|null = null;
  public user;
  public navState = 'closed';
  public hamburger = 'figurative';
  public disableShadow = false;

  constructor(
    private userData: UserDataService, private router: Router,
    private navService: NavService, private themeService: ThemeService,
    private weatherService: WeatherService,
  ) {

  }

  ngOnInit() {
    this.userSubscription = this.userData.user.subscribe({
      next: r => { this.user = r }
    });

    this.routerSubscription = this.router.events.subscribe({
      next: () => { this.navService.closeNav(); }
    });

    this.navServiceSubscription = this.navService.showNav.subscribe({
      next: v => {
        this.navState = v;

        if(this.nav.nativeElement.scroll) // for Edge >_>
          this.nav.nativeElement.scroll(0, 0);
      }
    });

    this.weatherSubscription = this.weatherService.weather.subscribe({
      next: w => {
        this.weather = w?.find(w => new Date().toISOString().startsWith(w.date)) || null;
      }
    });

    this.disableShadowSubscription = this.navService.disableHeaderShadow.subscribe({
      next: d => { this.disableShadow = d; }
    });

    this.hamburgerSubscription = this.themeService.hamburger.subscribe({
      next: v => {
        this.hamburger = v;
      }
    });
  }

  ngOnDestroy() {
    this.userSubscription.unsubscribe();
    this.routerSubscription.unsubscribe();
    this.navServiceSubscription.unsubscribe();
    this.disableShadowSubscription.unsubscribe();
    this.hamburgerSubscription.unsubscribe();
    this.weatherSubscription.unsubscribe();
  }

  doCloseNav()
  {
    this.navService.closeNav();
  }

  doClickHeader()
  {
    this.navService.toggleNav();
  }
}
