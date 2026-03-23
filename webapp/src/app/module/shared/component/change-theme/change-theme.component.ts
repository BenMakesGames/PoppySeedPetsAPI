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
