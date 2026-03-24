import {Component, ElementRef, OnDestroy, OnInit, ViewChild} from '@angular/core';
import {UserSessionService} from "../../service/user-session.service";
import { MyAccountSerializationGroup, UserMenuItem } from "../../model/my-account/my-account.serialization-group";
import {Subscription} from "rxjs";
import {UserDataService} from "../../service/user-data.service";
import { WeatherService } from "../../module/shared/service/weather.service";
import { ApiService } from "../../module/shared/service/api.service";

@Component({
    selector: 'app-menu',
    templateUrl: './menu.component.html',
    styleUrls: ['./menu.component.scss'],
    standalone: false
})
export class MenuComponent implements OnInit, OnDestroy {

  @ViewChild('skipTarget') skipTarget: ElementRef;

  private userSubscription = Subscription.EMPTY;
  public user: MyAccountSerializationGroup;

  private weatherSubscription = Subscription.EMPTY;
  public isHalloween = false;

  editing = false;
  savingMenuOrder = false;
  skipTargetTabIndex = null;
  revertOrder: UserMenuItem[] = [];
  menu: UserMenuItem[]|null = null;
  showKeyboardShortcuts = false;

  constructor(
    private userSession: UserSessionService, private userData: UserDataService, private weatherService: WeatherService,
    private api: ApiService
  ) { }

  ngOnInit()
  {
    this.userSubscription = this.userData.user.subscribe(u => {
      this.user = u;

      if(this.user)
      {
        if(this.user.menu && (!this.editing || this.savingMenuOrder))
          this.menu = this.user.menu.items.sort((a, b) => a.sortOrder - b.sortOrder);
      }
      else
      {
        this.menu = [];
      }
    });

    this.weatherSubscription = this.weatherService.weather.subscribe(e => {
      this.isHalloween = e && e.length > 0 && e[0].holidays.indexOf('Halloween') >= 0;
    });
  }

  doSkipMenu()
  {
    this.skipTargetTabIndex = 0;
    setTimeout(() => {
      this.skipTarget.nativeElement.focus();
    })
  }

  doSkipTargetBlur()
  {
    this.skipTargetTabIndex = null;
  }

  ngOnDestroy()
  {
    this.userSubscription.unsubscribe();
    this.weatherSubscription.unsubscribe();
  }

  doLogOut() {
    this.userSession.logOut();
  }

  doEditMenu()
  {
    this.revertOrder = [ ...this.menu ];
    this.editing = true;
  }

  doMoveUp(m: UserMenuItem)
  {
    this.doMoveMenuItem(m, -1);
  }

  doMoveDown(m: UserMenuItem)
  {
    this.doMoveMenuItem(m, 1);
  }

  private doMoveMenuItem(m: UserMenuItem, delta: number)
  {
    let i = this.menu.findIndex(um => um.location === m.location);

    const t = this.menu[i];
    this.menu[i] = this.menu[i + delta];
    this.menu[i + delta] = t;
  }

  doSaveEdit()
  {
    this.savingMenuOrder = true;

    const data = {
      order: this.menu.map(um => um.location)
    };

    this.api.patch('/account/menuOrder', data).subscribe({
      next: () => {
        this.editing = false;
        this.savingMenuOrder = false;
      },
      error: () => {
        this.savingMenuOrder = false;
      }
    })
  }

  doCancelEdit()
  {
    this.menu = this.revertOrder;
    this.editing = false;
  }

  readonly MENU_ITEMS = {
    home: {
      icon: 'home',
      name: 'Home',
      url: '/home',
      shortcutKey: 'shift.h',
    },
    cookingBuddy: {
      icon: 'cooking-buddy',
      name: 'Cooking Buddy',
      url: '/cookingBuddy',
      shortcutKey: 'shift.y',
    },
    basement: {
      icon: 'basement',
      name: 'Basement',
      url: '/basement',
      shortcutKey: 'shift.a',
    },
    infinityVault: {
      icon: 'infinity-vault',
      name: 'Infinity Vault',
      url: '/infinityVault',
      shortcutKey: 'shift.v',
    },
    greenhouse: {
      icon: 'greenhouse',
      name: 'Greenhouse',
      url: '/greenhouse',
      shortcutKey: 'shift.e',
    },
    beehive: {
      icon: 'beehive',
      name: 'Beehive',
      url: '/beehive',
      shortcutKey: 'shift.b',
    },
    dragonDen: {
      icon: 'dragon-den',
      name: 'Dragon Den',
      url: '/dragon',
      shortcutKey: 'shift.d',
    },
    library: {
      icon: 'library',
      name: 'Library',
      url: '/library',
      shortcutKey: 'shift.y',
    },
    fireplace: {
      icon: 'fireplace',
      name: 'Fireplace',
      url: '/fireplace',
      shortcutKey: 'shift.f',
    },
    park: {
      icon: 'park',
      name: 'Park',
      url: '/park',
      shortcutKey: 'shift.k',
    },
    plaza: {
      icon: 'plaza',
      name: 'Plaza',
      url: '/plaza',
      shortcutKey: 'shift.z',
    },
    museum: {
      icon: 'museum',
      name: 'Museum',
      url: '/museum',
      shortcutKey: 'shift.u',
    },
    market: {
      icon: 'market',
      name: 'Market',
      url: '/market',
      shortcutKey: 'shift.m',
    },
    grocer: {
      icon: 'grocer',
      name: 'Grocer',
      url: '/grocer',
      shortcutKey: 'shift.g',
    },
    petShelter: {
      icon: 'pet-shelter',
      name: 'Pet Shelter',
      url: '/petShelter',
      shortcutKey: 'shift.p',
    },
    bookstore: {
      icon: 'bookstore',
      name: 'Bookstore',
      url: '/bookstore',
      shortcutKey: 'shift.o',
    },
    trader: {
      icon: 'trader',
      name: 'Trader',
      url: '/trader',
      shortcutKey: 'shift.r',
    },
    hattier: {
      icon: 'hattier',
      name: 'Hattier',
      url: '/hattier',
      shortcutKey: 'shift.t',
    },
    fieldGuide: {
      icon: 'fieldGuide',
      name: 'Field Guide',
      url: '/fieldGuide',
      shortcutKey: 'shift.i',
    },
    mailbox: {
      icon: 'mailbox',
      name: 'Mailbox',
      url: '/mailbox',
      shortcutKey: 'shift.x',
    },
    painter: {
      icon: 'painter',
      name: 'Painter',
      url: '/painter',
      shortcutKey: 'shift.n',
    },
    florist: {
      icon: 'florist',
      name: 'Florist',
      url: '/florist',
      shortcutKey: 'shift.l',
    },
    journal: {
      icon: 'pet-logs',
      name: 'Journal',
      url: '/journal/pet',
      shortcutKey: 'shift.j',
    },
    achievements: {
      icon: 'achievements',
      name: 'Achievements',
      url: '/achievements',
      shortcutKey: 'shift.c',
    },
    zoologist: {
      icon: 'zoologist',
      name: 'Zoologist',
      url: '/zoologist',
      shortcutKey: 'shift.s',
    }
  };
}
