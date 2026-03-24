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
