/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, EventEmitter, Input, OnChanges, Output, SimpleChanges } from '@angular/core';
import { ThemeInterface } from "../../../../model/theme.interface";
import { MyThemeSerializationGroup } from "../../../../model/my-theme.serialization-group";
import { PublicThemeSerializationGroup } from "../../../../model/public-theme.serialization-group";

@Component({
    selector: 'app-theme-preview',
    templateUrl: './theme-preview.component.html',
    styleUrls: ['./theme-preview.component.scss'],
    standalone: false
})
export class ThemePreviewComponent implements OnChanges {

  ThemeTypeEnum = ThemeTypeEnum;

  @Input() theme: ThemeInterface;
  @Output() saveAs = new EventEmitter<ThemeInterface>();
  @Output() use = new EventEmitter<ThemeInterface>();
  @Output() edit = new EventEmitter<ThemeInterface>();

  canEdit = false;
  icon = '';
  title = '';
  themeType: ThemeTypeEnum;

  constructor() { }

  ngOnChanges(changes: SimpleChanges) {
    if('name' in this.theme)
    {
      this.canEdit = 'id' in this.theme;
      this.title = (<MyThemeSerializationGroup>this.theme).name;
      this.icon = '';
      this.themeType = this.title === 'Current' ? ThemeTypeEnum.myCurrent : ThemeTypeEnum.mySaved;
    }
    else
    {
      this.canEdit = false;
      this.themeType = ThemeTypeEnum.someoneElses;
      this.title = (<PublicThemeSerializationGroup>this.theme).user.name;
      this.icon = (<PublicThemeSerializationGroup>this.theme).user.icon;
    }
  }

  doSaveAs()
  {
    this.saveAs.emit(this.theme);
  }

  doUse()
  {
    this.use.emit(this.theme);
  }

  doEdit()
  {
    this.edit.emit(this.theme);
  }
}

enum ThemeTypeEnum
{
  myCurrent,
  mySaved,
  someoneElses
}