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
