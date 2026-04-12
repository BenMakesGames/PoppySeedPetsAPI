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
import { ThemeService } from "./theme.service";
import { Subscription } from "rxjs";

@Injectable({
  providedIn: 'root'
})
export class SoundsService {

  private soundReferences = new Map<string, number>();
  private soundCache = new Map<string, HTMLAudioElement>();

  private maxCacheSize = 20; // desired max number of items to keep in cache

  private musicVolumeSubscription = Subscription.EMPTY;

  constructor(private readonly themeService: ThemeService) {
    SoundsService.service = this;

    this.musicVolumeSubscription = themeService.musicVolume.subscribe({
      next: v => {
        this.onMusicVolumeChange(v);
      }
    })
  }

  // dangerous! only intended for use by the @HasSounds decorator!
  private static service: SoundsService|undefined;
  public static getService(): SoundsService { return SoundsService.service; }

  registerSounds(sounds: string[])
  {
    for(const sound of sounds)
    {
      const count = (this.soundReferences.get(sound) || 0) + 1;
      this.soundReferences.set(sound, count);

      if (count === 1)
        this.loadSound(sound);
    }
  }

  unregisterSounds(sounds: string[])
  {
    for (const sound of sounds)
    {
      const count = (this.soundReferences.get(sound) || 1) - 1;

      if (count <= 0)
      {
        this.soundReferences.delete(sound);

        // Only unload if cache is full
        if (this.soundCache.size > this.maxCacheSize)
          this.soundCache.delete(sound);
      }
      else
        this.soundReferences.set(sound, count);
    }
  }

  private async loadSound(soundId: string): Promise<void> {
    if(this.soundCache.has(soundId))
      return;

    const audio = new Audio(`/assets/sounds/${soundId}.mp3`);
    await new Promise((resolve, reject) => {
      audio.addEventListener('canplaythrough', resolve, { once: true });
      audio.addEventListener('error', reject, { once: true });
      audio.load();
    });
    this.soundCache.set(soundId, audio);
  }

  private previousRandomSound: string = '';

  public async playRandomSound(soundIds: string[]): Promise<void> {
    if(!soundIds.length) return;

    let possibleSounds = soundIds.filter(s => s !== this.previousRandomSound);

    if(possibleSounds.length == 0)
      possibleSounds = soundIds;

    const randomIndex = Math.floor(Math.random() * possibleSounds.length);
    this.previousRandomSound = possibleSounds[randomIndex];

    await this.playSound(possibleSounds[randomIndex]);
  }

  public async playSound(soundId: string): Promise<void> {
    const playVolume = Math.min(1, this.themeService.soundVolume.value);

    if(playVolume <= 0)
      return;

    const audio = this.soundCache.get(soundId);

    if (!audio)
    {
      console.warn(`Sound ${soundId} not loaded.`);
      return;
    }

    if (!this.soundReferences.has(soundId)) {
      console.warn(`Sound ${soundId} is in the cache, but has no reference! This is a reference leak, and probably a memory leak!`);
      return;
    }

    const clone = audio.cloneNode() as HTMLAudioElement;
    try {
      clone.volume = playVolume;
      await clone.play();
    } catch (error) {
      console.warn(`Failed to play sound ${soundId}:`, error);
    }
  }

  private currentSong: CurrentSong|null = null;

  public async playSong(songId: string): Promise<void> {
    if(this.currentSong?.songId === songId)
      return;

    if(this.currentSong)
      this.stopSong();

    const playVolume = Math.min(1, this.themeService.musicVolume.value);

    if(playVolume <= 0)
      return;

    const audio = new Audio(`/assets/music/${songId}.mp3`);
    audio.loop = true;
    audio.volume = playVolume;

    try
    {
      await audio.play();

      this.currentSong = {
        songId: songId,
        audio: audio,
      };
    }
    catch(error)
    {
      console.warn(`Failed to play song ${songId}:`, error);
    }
  }

  public stopSong() {
    if(this.currentSong)
    {
      this.currentSong.audio.pause();
      this.currentSong = null;
    }
  }

  private onMusicVolumeChange(volume: number) {
    if(this.currentSong)
    {
      if(volume === 0)
        this.stopSong();
      else
        this.currentSong.audio.volume = volume;
    }
  }
}

interface CurrentSong
{
  songId: string;
  audio: HTMLAudioElement;
}

export function HasSounds(sounds: string[]) {
  return function(constructor: any)
  {
    const original = constructor.prototype.ngOnInit;
    const originalDestroy = constructor.prototype.ngOnDestroy;

    constructor.prototype.ngOnInit = function() {
      if (original) {
        original.apply(this);
      }
      SoundsService.getService().registerSounds(sounds);
    };

    constructor.prototype.ngOnDestroy = function() {
      if (originalDestroy) {
        originalDestroy.apply(this);
      }
      SoundsService.getService().unregisterSounds(sounds);
    };
  }
}
