/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, OnDestroy, OnInit} from '@angular/core';
import {ThemeService} from "../../../../shared/service/theme.service";
import {Subscription} from "rxjs";

@Component({
    templateUrl: './appearance.component.html',
    styleUrls: ['./appearance.component.scss'],
    standalone: false
})
export class AppearanceComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Settings - Visuals' };

  hamburgerSubscription = Subscription.EMPTY;
  hamburger = 'figurative';

  spiritCompanionsSubscription = Subscription.EMPTY;
  spiritCompanions = 'animated';

  fontWeightSubscription = Subscription.EMPTY;
  fontWeight = '';

  inventoryButtonsSubscription = Subscription.EMPTY;
  inventoryButtons = 'both';

  timeFormatSubscription = Subscription.EMPTY;
  timeFormat = '12hr';

  constructor(private themeService: ThemeService) {
  }

  ngOnInit() {
    this.hamburgerSubscription = this.themeService.hamburger.subscribe({
      next: v => { this.hamburger = v; }
    });

    this.spiritCompanionsSubscription = this.themeService.spiritCompanionAnimations.subscribe({
      next: v => { this.spiritCompanions = v; }
    });

    this.fontWeightSubscription = this.themeService.fontTheme.subscribe({
      next: v => { this.fontWeight = v; }
    });

    this.inventoryButtonsSubscription = this.themeService.inventoryButtons.subscribe({
      next: v => { this.inventoryButtons = v; }
    });

    this.timeFormatSubscription = this.themeService.timeFormat.subscribe({
      next: v => { this.timeFormat = v; }
    });
  }

  ngOnDestroy()
  {
    this.hamburgerSubscription.unsubscribe();
    this.spiritCompanionsSubscription.unsubscribe();
    this.fontWeightSubscription.unsubscribe();
    this.inventoryButtonsSubscription.unsubscribe();
    this.timeFormatSubscription.unsubscribe();
  }

  doSetFontWeight(weight: string)
  {
    this.themeService.changeFontTheme(weight);
  }

  doSetHamburger(hamburger: string)
  {
    this.themeService.setHamburger(hamburger);
  }

  doSetSpiritCompanions(animation: string)
  {
    this.themeService.setSpiritCompanionAnimations(animation);
  }

  doSetInventoryButtons(show: string)
  {
    this.themeService.setInventoryButtons(show);
  }

  doSetTimeFormat(format: string)
  {
    this.themeService.setTimeFormat(format);
  }
}
