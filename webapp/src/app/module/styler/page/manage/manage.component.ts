import { Component, OnDestroy, OnInit } from '@angular/core';
import { ApiService } from "../../../shared/service/api.service";
import { Subscription } from "rxjs";
import { MyThemeSerializationGroup } from "../../../../model/my-theme.serialization-group";
import { SaveAsDialog } from "../../dialog/save-as/save-as.dialog";
import { EditThemeDialog } from "../../dialog/edit-theme/edit-theme.dialog";
import { ThemeService } from "../../../shared/service/theme.service";
import { ThemeInterface } from "../../../../model/theme.interface";
import { MatDialog } from "@angular/material/dialog";

@Component({
    templateUrl: './manage.component.html',
    styleUrls: ['./manage.component.scss'],
    standalone: false
})
export class ManageComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'The Painter - My Themes' };

  useThemeSubscription = Subscription.EMPTY;
  getThemesSubscription = Subscription.EMPTY;
  themes: MyThemeSerializationGroup[] = [];

  constructor(private api: ApiService, private matDialog: MatDialog, private themeService: ThemeService) { }

  ngOnInit(): void {
    this.loadThemes();
  }

  private loadThemes()
  {
    this.getThemesSubscription.unsubscribe();

    this.getThemesSubscription = this.api.get<MyThemeSerializationGroup[]>('/style').subscribe({
      next: r => {
        this.themes = r.data.sort((a, b) => {
          if(a.name.toLowerCase() === 'current')
            return -1;
          else if(b.name.toLowerCase() === 'current')
            return 1;
          else
            return a.name.toLowerCase().localeCompare(b.name.toLowerCase());
        });
      }
    });
  }

  ngOnDestroy() {
    this.getThemesSubscription.unsubscribe();
  }

  doUse(theme: MyThemeSerializationGroup)
  {
    this.useThemeSubscription.unsubscribe();

    this.useThemeSubscription = this.api.patch<ThemeInterface>('/style/' + theme.id + '/setCurrent').subscribe({
      next: r => {
        this.themeService.setTheme(r.data);

        const currentThemeIndex = this.themes.findIndex(t => t.name === 'Current');

        if(currentThemeIndex === -1)
        {
          this.loadThemes();
        }
        else
        {
          this.themes[currentThemeIndex] = {
            ...this.themes[currentThemeIndex],
            ...r.data
          };
        }
      }
    });
  }

  doEdit(theme: MyThemeSerializationGroup)
  {
    EditThemeDialog.open(this.matDialog, theme).afterClosed().subscribe({
      next: r => {
        if(r.deleted)
        {
          this.themes = this.themes.filter(t => t.id !== theme.id);
        }
        else if(r.renamed)
        {
          theme.name = r.renamed;
        }
      }
    });
  }

  doSaveTheme()
  {
    SaveAsDialog.open(this.matDialog).afterClosed().subscribe({
      next: saved => {
        if(saved)
          this.loadThemes();
      }
    });
  }
}
