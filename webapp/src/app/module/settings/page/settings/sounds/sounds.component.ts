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
    templateUrl: './sounds.component.html',
    styleUrls: ['./sounds.component.scss'],
    standalone: false
})
export class SoundsComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Settings - Audio' };

  soundVolumeSubscription = Subscription.EMPTY;
  soundVolume = 'on';

  musicVolumeSubscription = Subscription.EMPTY;
  musicVolume = 'on';

  constructor(private readonly themeService: ThemeService) {
  }

  ngOnInit() {
    this.soundVolumeSubscription = this.themeService.soundVolume.subscribe({
      next: v => this.soundVolume = v > 0 ? 'on' : 'off'
    });

    this.musicVolumeSubscription = this.themeService.musicVolume.subscribe({
      next: v => this.musicVolume = v > 0 ? 'on' : 'off'
    });
  }

  ngOnDestroy()
  {
    this.soundVolumeSubscription.unsubscribe();
    this.musicVolumeSubscription.unsubscribe();
  }

  doSetSoundVolume(volume: number)
  {
    this.themeService.setSoundVolume(volume);
  }

  doSetMusicVolume(volume: number)
  {
    this.themeService.setMusicVolume(volume);
  }
}
