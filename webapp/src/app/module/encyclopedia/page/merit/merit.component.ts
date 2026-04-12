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
import {ApiService} from "../../../shared/service/api.service";
import {MeritEncyclopediaSerializationGroup} from "../../../../model/encyclopedia/merit-encyclopedia.serialization-group";
import {FilterResultsSerializationGroup} from "../../../../model/filter-results.serialization-group";
import {Subscription} from "rxjs";
import { ActivatedRoute, ParamMap } from "@angular/router";
import { QueryStringService } from "../../../../service/query-string.service";

@Component({
    templateUrl: './merit.component.html',
    styleUrls: ['./merit.component.scss'],
    standalone: false
})
export class MeritComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Poppyopedia - Merits' };

  results: FilterResultsSerializationGroup<MeritEncyclopediaSerializationGroup>;
  loading = false;

  meritAjax: Subscription;
  page = 0;

  constructor(private api: ApiService, private activatedRoute: ActivatedRoute) {
  }

  ngOnInit() {
    this.activatedRoute.queryParamMap.subscribe({
      next: (p: ParamMap) =>
      {
        const params = QueryStringService.parse(p);

        if('page' in params)
          this.page = QueryStringService.parseInt(params.page, 0);
        else
          this.page = 0;

        this.doSearch();
      }
    });
  }

  ngOnDestroy(): void {
    this.meritAjax.unsubscribe();
  }

  doSearch()
  {
    if(this.loading) return;

    this.loading = true;

    this.meritAjax = this.api.get<FilterResultsSerializationGroup<MeritEncyclopediaSerializationGroup>>('/encyclopedia/merit', { page: this.page }).subscribe(
      r => {
        this.results = r.data;

        this.results.results = this.results.results.map(m => {
          return {
            name: m.name,
            description: this.formatDescription(m.description)
          }
        });

        this.loading = false;
      }
    );
  }

  private formatDescription(description: string)
  {
    let formatted = description.replace(/%pet\.name%/g, 'this pet');
    return formatted[0].toUpperCase() + formatted.substr(1);
  }
}
