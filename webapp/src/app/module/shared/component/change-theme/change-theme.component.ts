/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, EventEmitter, OnInit, Output } from '@angular/core';
import {ThemeService} from "../../service/theme.service";
import { BuiltInThemeSerializationGroup } from "../../../../model/built-in-theme.serialization-group";
import { CommonModule } from "@angular/common";

@Component({
    selector: 'app-change-theme',
    templateUrl: './change-theme.component.html',
    imports: [
        CommonModule
    ],
    styleUrls: ['./change-theme.component.scss']
})
export class ChangeThemeComponent implements OnInit {

  @Output() change = new EventEmitter<BuiltInThemeSerializationGroup>();

  themes: BuiltInThemeSerializationGroup[];
  currentThemeIndex: number = 0;

  constructor(private themeService: ThemeService) {
    this.themes = ThemeService.Themes;
  }

  ngOnInit() {
  }

  doSetTheme(theme: BuiltInThemeSerializationGroup, index: number)
  {
    this.currentThemeIndex = index;
    this.themeService.setTheme(theme);

    this.change.emit(theme);
  }
}
