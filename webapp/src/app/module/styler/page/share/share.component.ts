/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, OnInit } from '@angular/core';
import { PublicThemeSerializationGroup } from "../../../../model/public-theme.serialization-group";
import { ApiService } from "../../../shared/service/api.service";
import { FilterResultsSerializationGroup } from "../../../../model/filter-results.serialization-group";
import { Subscription } from "rxjs";
import { ActivatedRoute, Params } from "@angular/router";
import { ThemeInterface } from "../../../../model/theme.interface";
import { ThemeService } from "../../../shared/service/theme.service";

@Component({
    templateUrl: './share.component.html',
    styleUrls: ['./share.component.scss'],
    standalone: false
})
export class ShareComponent implements OnInit {
  pageMeta = { title: 'The Painter - Player Themes' };

  useThemeSubscription = Subscription.EMPTY;
  searchSubscription = Subscription.EMPTY;
  results: FilterResultsSerializationGroup<PublicThemeSerializationGroup>|undefined;

  constructor(
    private api: ApiService, private activatedRoute: ActivatedRoute,
    private themeService: ThemeService
  ) { }

  ngOnInit(): void {
    this.activatedRoute.queryParams.subscribe({
      next: (p: Params) => {
        let page = 0;

        if('page' in p)
          page = p.page;

        this.search(page);
      }
    });
  }

  doChangePage(page: number)
  {
    this.search(page);
  }

  doUse(theme: PublicThemeSerializationGroup)
  {
    this.useThemeSubscription.unsubscribe();

    this.useThemeSubscription = this.api.patch<ThemeInterface>('/style/' + theme.id + '/setCurrent').subscribe({
      next: r => {
        this.themeService.setTheme(r.data);
      }
    });

  }

  private search(page: number)
  {
    const data = {
      page: page,
      filter: {
        following: true,
      }
    };

    this.searchSubscription = this.api.get<FilterResultsSerializationGroup<PublicThemeSerializationGroup>>('/style/following', data).subscribe({
      next: r => {
        this.results = r.data;
      }
    });
  }

}
