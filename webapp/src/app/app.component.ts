/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component} from '@angular/core';
import {Router} from "@angular/router";
import {ThemeService} from "./module/shared/service/theme.service";
import {UpdateService} from "./service/update.service";
import {NavService} from "./service/nav.service";
import {DeviceStatsService} from "./service/device-stats.service";
import {Title} from "@angular/platform-browser";
import { SoundsService } from "./module/shared/service/sounds.service";

@Component({
    selector: 'app-root',
    templateUrl: './app.component.html',
    styleUrls: ['./app.component.scss'],
    standalone: false
})
export class AppComponent {
  title = 'PoppySeedPets';

  public constructor(
    private readonly router: Router, private readonly themeService: ThemeService,
    private readonly update: UpdateService, private readonly navService: NavService,
    private readonly deviceStats: DeviceStatsService, private readonly titleService: Title,
    private readonly sounds: SoundsService
  )
  {
    console.log(`  _
 |_) _  ._  ._
 |  (_) |_) |_) \\/
   __   |   |   /
  (_   _   _   _|
  __) (/_ (/_ (_|
    _
   |_) _ _|_  _
   |  (/_ |_ _>

`);

    if(Math.random() < 0.1)
      AppComponent.consoleLogRandomSecret();

    this.router.onSameUrlNavigation = 'reload';
  }

  doRouterActivate(pageComponent)
  {
    window.scroll(0, 0);

    const newTitle = pageComponent.pageMeta?.title;

    if(newTitle)
      this.titleService.setTitle('Poppy Seed Pets - ' + newTitle);
    else
      this.titleService.setTitle('Poppy Seed Pets');

    const newSong = pageComponent.pageMeta?.song;

    if(newSong === PreservePreviousSong)
    { } // do nothing
    else if(newSong)
      this.sounds.playSong(newSong);
    else
      this.sounds.stopSong();
  }

  private static loggedASecret = false;

  public static consoleLogRandomSecret()
  {
    if(AppComponent.loggedASecret)
      return;

    const secrets = [
      'https://poppyseedpets.com/assets/images/DLkZK1na.png',
      'https://poppyseedpets.com/assets/images/rxgTIVl4.png',
      'UmVjaXBlOiBTdWdhciArIEdyZWVuIG9yIFllbGxvdyBEeWU=',
      'ZXF1aXAgSGVhcnRzdG9uZSB0byBhY2Nlc3MgdGhlIEhlYXJ0IERpbWVuc2lvbg==',
    ];

    console.log('★ ☆ ★ ☆ ★\n\n' + secrets[Math.floor(Math.random() * secrets.length)] + '\n\n★ ☆ ★ ☆ ★');

    this.loggedASecret = true;
  }
}

export const PreservePreviousSong = Symbol();
