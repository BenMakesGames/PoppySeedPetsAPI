/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Inject, Injectable, DOCUMENT} from '@angular/core';
import {BehaviorSubject} from "rxjs";

import { ThemeInterface } from "../../../model/theme.interface";
import { BuiltInThemeSerializationGroup } from "../../../model/built-in-theme.serialization-group";

const DefaultSoundVolume = 0;
const DefaultMusicVolume = 0;

@Injectable({
  providedIn: 'root'
})
export class ThemeService {

  public static readonly FontThemes = {
    '': 'Normal',
    'thin': 'Thin',
  };

  public static readonly Hamburgers = [
    'figurative', 'literal', 'invisible'
  ];

  public static readonly SpiritCompanionAnimations = [
    'animated', 'static', 'hidden'
  ];

  public static readonly ItemSorts = [
    'modifiedOn', 'name'
  ];

  public static readonly MultiSelectWith = [
    'longPress', 'doubleClick', 'never'
  ];

  public static readonly NumberOfLegs = [
    -2, 0, 1, 2, 3, 4, 6, 8, 9, 10, 12, 100
  ];

  public static readonly InventoryButtons = [
    'both', 'text-only', 'icon-only'
  ]

  public fontTheme = new BehaviorSubject<string>('thin');
  public hamburger = new BehaviorSubject<string>('figurative');
  public spiritCompanionAnimations = new BehaviorSubject<string>('animated');
  public defaultHouseSort = new BehaviorSubject<string>('modifiedOn');
  public numberOfLegs = new BehaviorSubject<number>(2);
  //public multiSelectWith = new BehaviorSubject<string>('longPress');
  public multiSelectWith = new BehaviorSubject<string>('doubleClick');
  public inventoryButtons = new BehaviorSubject<string>('both');
  public timeFormat = new BehaviorSubject<string>('12hr');
  public soundVolume = new BehaviorSubject<number>(DefaultSoundVolume);
  public musicVolume = new BehaviorSubject<number>(DefaultMusicVolume);

  constructor(@Inject(DOCUMENT) private document: Document) {
    this.fontTheme.subscribe({
      next: (t) => {
        if(this.document.documentElement.classList.length)
          this.document.documentElement.classList.remove(...(Object.keys(ThemeService.FontThemes).filter(t => t !== '')));

        if(t)
          this.document.documentElement.classList.add(t);
      }
    });

    // show inventory button icons
    const inventoryButtons = localStorage.getItem('inventoryButtons');
    if(ThemeService.InventoryButtons.indexOf(inventoryButtons) >= 0)
      this.inventoryButtons.next(inventoryButtons);

    // font theme
    const fontTheme = localStorage.getItem('fontTheme');

    if(Object.keys(ThemeService.FontThemes).indexOf(fontTheme) >= 0)
      this.fontTheme.next(fontTheme);

    // hamburger menu appearance
    const hamburger = localStorage.getItem('hamburger');

    if(ThemeService.Hamburgers.indexOf(hamburger) >= 0)
      this.hamburger.next(hamburger);

    // spirit companion animations
    const spiritCompanionAnimations = localStorage.getItem('spiritCompanionAnimations');

    if(ThemeService.SpiritCompanionAnimations.indexOf(spiritCompanionAnimations) >= 0)
      this.spiritCompanionAnimations.next(spiritCompanionAnimations);

    // default house sort
    const sort = localStorage.getItem('defaultHouseSort');

    if(ThemeService.ItemSorts.indexOf(sort) >= 0)
      this.defaultHouseSort.next(sort);

    // default house sort
    /*
    const multiSelectWith = localStorage.getItem('multiSelectWith');

    if(ThemeService.MULTI_SELECT_WITH.indexOf(multiSelectWith) >= 0)
      this.multiSelectWith.next(multiSelectWith);
    */
    this.multiSelectWith.next('doubleClick');

    // number of legs... >_>
    const numberOfLegs = parseInt(localStorage.getItem('numberOfLegs'));

    if(ThemeService.NumberOfLegs.indexOf(numberOfLegs) >= 0)
      this.numberOfLegs.next(numberOfLegs);

    // time format (12hr or 24hr)
    const timeFormat = localStorage.getItem('timeFormat');

    this.timeFormat.next(timeFormat ?? '12hr');

    // sound volume
    const soundVolume = parseFloat(localStorage.getItem('soundVolume'));

    if(soundVolume >= 0 && soundVolume <= 1)
      this.soundVolume.next(soundVolume);
    else
      this.soundVolume.next(DefaultSoundVolume);

    // music volume
    const musicVolume = parseFloat(localStorage.getItem('musicVolume'));

    if(musicVolume >= 0 && musicVolume <= 1)
      this.musicVolume.next(musicVolume);
    else
      this.musicVolume.next(DefaultMusicVolume);
  }

  setInventoryButtons(show: string)
  {
    this.setPreference<string>('inventoryButtons', show, ThemeService.InventoryButtons);
  }

  setHamburger(hamburger: string)
  {
    this.setPreference<string>('hamburger', hamburger, ThemeService.Hamburgers);
  }

  setSpiritCompanionAnimations(animations: string)
  {
    this.setPreference<string>('spiritCompanionAnimations', animations, ThemeService.SpiritCompanionAnimations);
  }

  changeFontTheme(t: string)
  {
    this.setPreference<string>('fontTheme', t, Object.keys(ThemeService.FontThemes));
  }

  setDefaultHouseSort(sort: string)
  {
    this.setPreference<string>('defaultHouseSort', sort, ThemeService.ItemSorts);
  }

  setMultiSelectWith(interaction: string)
  {
    this.setPreference<string>('multiSelectWith', interaction, ThemeService.MultiSelectWith);
  }

  private setPreference<T>(preference: string, newValue: T, allowedValues: T[])
  {
    if(allowedValues.indexOf(newValue) === -1) return;

    this[preference].next(newValue);

    localStorage.setItem(preference, newValue.toString());
  }

  setNumberOfLegs(n: number)
  {
    this.setPreference<number>('numberOfLegs', n, ThemeService.NumberOfLegs);
  }

  setTimeFormat(f: string)
  {
    this.setPreference<string>('timeFormat', f, [ '12hr', '24hr' ]);
  }

  setSoundVolume(volume: number)
  {
    this.setPreference<number>('soundVolume', volume, [0, 0.25, 0.5, 0.75, 1]);
  }

  setMusicVolume(volume: number)
  {
    this.setPreference<number>('musicVolume', volume, [0, 0.25, 0.5, 0.75, 1]);
  }

  getTheme(): ThemeInterface
  {
    return <ThemeInterface>{
      backgroundColor: this.getStyleColor('color-content-background') ?? ThemeService.Themes[0].backgroundColor,
      speechBubbleBackgroundColor: this.getStyleColor('color-speech-bubble-background') ?? ThemeService.Themes[0].speechBubbleBackgroundColor,
      textColor: this.getStyleColor('color-text-on-content-background') ?? ThemeService.Themes[0].textColor,
      primaryColor: this.getStyleColor('color-primary') ?? ThemeService.Themes[0].primaryColor,
      textOnPrimaryColor: this.getStyleColor('color-text-on-primary') ?? ThemeService.Themes[0].textOnPrimaryColor,
      tabBarBackgroundColor: this.getStyleColor('color-tab-bar-background') ?? ThemeService.Themes[0].tabBarBackgroundColor,
      linkAndButtonColor: this.getStyleColor('color-link-and-button') ?? ThemeService.Themes[0].linkAndButtonColor,
      buttonTextColor: this.getStyleColor('color-text-on-button') ?? ThemeService.Themes[0].buttonTextColor,
      dialogLinkColor: this.getStyleColor('color-link-on-speech-bubble') ?? ThemeService.Themes[0].dialogLinkColor,
      warningColor: this.getStyleColor('color-warning') ?? ThemeService.Themes[0].warningColor,
      gainColor: this.getStyleColor('color-gain') ?? ThemeService.Themes[0].gainColor,
      bonusAndSpiceColor: this.getStyleColor('color-tool-bonus') ?? ThemeService.Themes[0].bonusAndSpiceColor,
      bonusAndSpiceSelectedColor: this.getStyleColor('color-tool-bonus-on-primary') ?? ThemeService.Themes[0].bonusAndSpiceSelectedColor,
      inputBackgroundColor: this.getStyleColor('color-input-background') ?? ThemeService.Themes[0].inputBackgroundColor,
      inputTextColor: this.getStyleColor('color-input-foreground') ?? ThemeService.Themes[0].inputTextColor,
    };
  }

  setTheme(theme: ThemeInterface|null)
  {
    if(theme === null)
      return;

    this.setStyleColor('color-content-background', theme.backgroundColor);
    this.setStyleColor('color-speech-bubble-background', theme.speechBubbleBackgroundColor);
    this.setStyleColor('color-text-on-content-background', theme.textColor);
    this.setStyleColor('color-primary', theme.primaryColor);
    this.setStyleColor('color-text-on-primary', theme.textOnPrimaryColor);
    this.setStyleColor('color-tab-bar-background', theme.tabBarBackgroundColor);
    this.setStyleColor('color-link-and-button', theme.linkAndButtonColor);
    this.setStyleColor('color-text-on-button', theme.buttonTextColor);
    this.setStyleColor('color-link-on-speech-bubble', theme.dialogLinkColor);
    this.setStyleColor('color-warning', theme.warningColor);
    this.setStyleColor('color-gain', theme.gainColor);
    this.setStyleColor('color-tool-bonus', theme.bonusAndSpiceColor);
    this.setStyleColor('color-tool-bonus-on-primary', theme.bonusAndSpiceSelectedColor);
    this.setStyleColor('color-input-background', theme.inputBackgroundColor);
    this.setStyleColor('color-input-foreground', theme.inputTextColor);
  }

  getStyleColor(name: string): string|null
  {
    const rgb = getComputedStyle(document.documentElement).getPropertyValue('--' + name)
      .split(/ *, */)
      .map(v => parseInt(v))
    ;

    if(rgb.length !== 3 || rgb.some(v => isNaN(v)))
      return null;

    return ThemeService.rgbToHex(rgb[0], rgb[1], rgb[2]);
  }

  setStyleColor(name: string, value: string): boolean
  {
    const rgb = ThemeService.hexToRgb(value);

    if(!rgb)
      return false;

    document.documentElement.style.setProperty('--' + name, rgb.join(', '));

    return true;
  }

  static rgbToHex(r: number, g: number, b: number): string|null
  {
    if(r < 0 || r > 255 || g < 0 || g > 255 || b < 0 || b > 255)
      return null;

    return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
  }

  static hexToRgb(hex: string): number[]|null
  {
    let result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);

    if(result.length !== 4)
      return null;

    const colors = result.slice(1).map(v => parseInt(v, 16));

    if(colors.some(c => isNaN(c)))
      return null;

    return colors;
  }

  public static readonly EventThemes = {
    'Apricot Festival': {
      name: 'Apricot Festival',
      backgroundColor: 'EEDAC9',
      primaryColor: '63ECE0',
      bonusAndSpiceColor: 'C7690B',
      bonusAndSpiceSelectedColor: 'FFBA25',
      petInfoBackgroundColor: '4BA629',
      tabBarBackgroundColor: '14DE23',
      speechBubbleBackgroundColor: 'FF9B3F',

      textColor: '000000',
      textOnPrimaryColor: '000000',

      linkAndButtonColor: '32912A',
      dialogLinkColor: 'FEFF94',
      buttonTextColor: 'ffffff',

      warningColor: 'cc4422',
      gainColor: '228844',

      inputBackgroundColor: 'ffffff',
      inputTextColor: '333333',
    },
    'Talk Like a Pirate Day': {
      name: 'Yarr!',
      backgroundColor: '222222',
      primaryColor: '882222',
      bonusAndSpiceColor: 'CCC500',
      bonusAndSpiceSelectedColor: 'CCC500',
      petInfoBackgroundColor: '000000',
      tabBarBackgroundColor: '555555',
      speechBubbleBackgroundColor: '000000',

      textColor: 'eeeeee',
      textOnPrimaryColor: 'ffffff',

      linkAndButtonColor: 'B43131',
      dialogLinkColor: 'CBB933',
      buttonTextColor: 'ffffff',

      warningColor: 'cc4422',
      gainColor: '228844',

      inputBackgroundColor: 'ffffff',
      inputTextColor: '333333',
    },
    'Halloween': {
      name: 'Spoooooky!',
      backgroundColor: '222222',
      primaryColor: 'C86015',
      bonusAndSpiceColor: 'FFE100',
      bonusAndSpiceSelectedColor: 'FFE100',
      petInfoBackgroundColor: '000000',
      tabBarBackgroundColor: '375B39',
      speechBubbleBackgroundColor: '000000',

      textColor: 'eeeeee',
      textOnPrimaryColor: 'ffffff',

      linkAndButtonColor: 'FFB600',
      dialogLinkColor: 'CBB933',
      buttonTextColor: '000000',

      warningColor: 'cc4422',
      gainColor: '228844',

      inputBackgroundColor: 'ffffff',
      inputTextColor: '333333',
    },
    'Saint Patrick\'s': {
      name: 'Hills of Gold',
      backgroundColor: 'DCF8D9',
      primaryColor: '5CCE40',
      bonusAndSpiceColor: 'A88F0A',
      bonusAndSpiceSelectedColor: 'FDFF00',
      petInfoBackgroundColor: '5ABF46',
      tabBarBackgroundColor: 'DAD42E',
      speechBubbleBackgroundColor: 'ffffff',

      textColor: '222222',
      textOnPrimaryColor: '000000',

      linkAndButtonColor: '199D00',
      dialogLinkColor: 'FFF686',
      buttonTextColor: 'ffffff',

      warningColor: 'cc4422',
      gainColor: '228844',

      inputBackgroundColor: 'ffffff',
      inputTextColor: '333333',
    }
  };

  public static readonly Themes: BuiltInThemeSerializationGroup[] = [
    {
      name: 'Light',
      backgroundColor: 'eeeeee',
      primaryColor: '225588',
      bonusAndSpiceColor: '009999',
      bonusAndSpiceSelectedColor: '00cccc',
      petInfoBackgroundColor: 'ffffff',
      tabBarBackgroundColor: 'bbbbbb',
      speechBubbleBackgroundColor: 'ffffff',

      textColor: '333333',
      textOnPrimaryColor: 'ffffff',

      linkAndButtonColor: '4477aa',
      dialogLinkColor: '4477aa',
      buttonTextColor: 'ffffff',

      warningColor: 'cc4422',
      gainColor: '228844',

      inputBackgroundColor: 'ffffff',
      inputTextColor: '333333',
    },
    {
      name: 'Dark',
      backgroundColor: '222222',
      primaryColor: '225588',
      bonusAndSpiceColor: '00cccc',
      bonusAndSpiceSelectedColor: '00cccc',
      petInfoBackgroundColor: '000000',
      tabBarBackgroundColor: '555555',
      speechBubbleBackgroundColor: '000000',

      textColor: 'eeeeee',
      textOnPrimaryColor: 'ffffff',

      linkAndButtonColor: '4477aa',
      dialogLinkColor: '4477aa',
      buttonTextColor: 'ffffff',

      warningColor: 'cc4422',
      gainColor: '228844',

      inputBackgroundColor: 'ffffff',
      inputTextColor: '333333',
    },
    {
      name: 'Poppy',
      backgroundColor: 'eeeeee',
      primaryColor: 'cc4422',
      bonusAndSpiceColor: 'bb3300',
      bonusAndSpiceSelectedColor: 'ffbb99',
      petInfoBackgroundColor: 'ffffff',
      tabBarBackgroundColor: 'bbbbbb',
      speechBubbleBackgroundColor: 'ffffff',

      textColor: '333333',
      textOnPrimaryColor: 'ffffff',

      linkAndButtonColor: '448844',
      dialogLinkColor: '448844',
      buttonTextColor: 'ffffff',

      warningColor: 'cc4422',
      gainColor: '228844',

      inputBackgroundColor: 'ffffff',
      inputTextColor: '333333',
    },
    {
      name: 'Khaki',
      backgroundColor: 'eeeeee',
      primaryColor: '806c3c',
      bonusAndSpiceColor: 'bb7700',
      bonusAndSpiceSelectedColor: 'eecc77',
      petInfoBackgroundColor: 'ffffff',
      tabBarBackgroundColor: 'bbbbbb',
      speechBubbleBackgroundColor: 'ffffff',

      textColor: '333333',
      textOnPrimaryColor: 'ffffff',

      linkAndButtonColor: 'a78e4e',
      dialogLinkColor: 'a78e4e',
      buttonTextColor: 'ffffff',

      warningColor: 'cc4422',
      gainColor: '228844',

      inputBackgroundColor: 'ffffff',
      inputTextColor: '333333',
    },
    {
      name: 'Black & Pink',
      backgroundColor: '222222',
      primaryColor: '993377',
      bonusAndSpiceColor: 'cc00cc',
      bonusAndSpiceSelectedColor: 'ee77ff',
      petInfoBackgroundColor: '000000',
      tabBarBackgroundColor: '662255',
      speechBubbleBackgroundColor: '000000',

      textColor: 'eeeeee',
      textOnPrimaryColor: 'ffffff',

      linkAndButtonColor: 'cc44aa',
      dialogLinkColor: 'cc44aa',
      buttonTextColor: 'ffffff',

      warningColor: 'cc4422',
      gainColor: '228844',

      inputBackgroundColor: 'ffffff',
      inputTextColor: '333333',
    },
    {
      name: 'Twilight Ace',
      backgroundColor: '3b3b3b',
      primaryColor: '663399',
      textOnPrimaryColor: 'ffffff',
      bonusAndSpiceColor: 'ee88ee',
      bonusAndSpiceSelectedColor: 'ee99ee',
      petInfoBackgroundColor: '775994',
      tabBarBackgroundColor: '6a597a',
      speechBubbleBackgroundColor: '775994',
      textColor: 'ffffff',
      linkAndButtonColor: 'a05de3',
      dialogLinkColor: 'bbaacc',
      buttonTextColor: 'ffffff',
      warningColor: 'cc4422',
      gainColor: '228844',
      inputBackgroundColor: 'ffffff',
      inputTextColor: '333333',
    },
    {
      name: 'Bananas Foster',
      backgroundColor: 'eeeeee',
      primaryColor: 'aa8800',
      bonusAndSpiceColor: 'aa7744',
      bonusAndSpiceSelectedColor: 'eecc33',
      petInfoBackgroundColor: 'ffffff',
      tabBarBackgroundColor: 'cccc99',
      speechBubbleBackgroundColor: 'ffffff',

      textColor: '333333',
      textOnPrimaryColor: 'ffffff',

      linkAndButtonColor: '006600',
      dialogLinkColor: '006600',
      buttonTextColor: 'ffffff',

      warningColor: 'cc4422',
      gainColor: '228844',

      inputBackgroundColor: 'ffffff',
      inputTextColor: '333333',
    },
    {
      name: 'Protocol 7',
      backgroundColor: '222222',
      primaryColor: '52c835',
      bonusAndSpiceColor: 'ddbb00',
      bonusAndSpiceSelectedColor: '775500',
      petInfoBackgroundColor: '254c1e',
      tabBarBackgroundColor: '254c1e',
      speechBubbleBackgroundColor: '000000',

      textColor: 'eeeeee',
      textOnPrimaryColor: '333333',

      linkAndButtonColor: '199d00',
      dialogLinkColor: '199d00',
      buttonTextColor: 'ffffff',

      warningColor: 'cc4422',
      gainColor: '228844',

      inputBackgroundColor: 'ffffff',
      inputTextColor: '333333',
    },
  ];
}
