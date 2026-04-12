/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, OnDestroy, OnInit } from '@angular/core';
import { ApiService } from "../../../shared/service/api.service";
import { ActivatedRoute } from "@angular/router";
import { FilterResultsSerializationGroup } from "../../../../model/filter-results.serialization-group";
import { ArticleSerializationGroup } from "../../../../model/article.serialization-group";
import { ApiResponseModel } from "../../../../model/api-response.model";
import { Subscription } from "rxjs";

@Component({
    templateUrl: './design-goal-details.component.html',
    styleUrls: ['./design-goal-details.component.scss'],
    standalone: false
})
export class DesignGoalDetailsComponent implements OnInit, OnDestroy {

  page: number = 0;
  results: FilterResultsSerializationGroup<ArticleSerializationGroup>;
  articleSearchAjax = Subscription.EMPTY;

  designGoal: { id: number, name: string, description: string }|null = null;

  constructor(private api: ApiService, private activatedRoute: ActivatedRoute) { }

  ngOnInit(): void {
    this.activatedRoute.paramMap.subscribe(params => {
      this.designGoal = null;

      this.api.get<{ name: string, id: number, description: string }>('/designGoal/' + params.get('designGoal')).subscribe({
        next: r => {
          this.designGoal = r.data;

          this.doSearch();
        }
      });
    });
  }

  ngOnDestroy()
  {
    this.articleSearchAjax.unsubscribe();
  }

  doSearch()
  {
    this.articleSearchAjax.unsubscribe();

    this.results = null;

    const data = {
      page: this.page,
      filter: {
        designGoal: this.designGoal.id
      }
    };

    this.articleSearchAjax = this.api.get<FilterResultsSerializationGroup<ArticleSerializationGroup>>('/article', data).subscribe(
      (r: ApiResponseModel<FilterResultsSerializationGroup<ArticleSerializationGroup>>) => {
        this.results = r.data;
        this.page = r.data.page;
      }
    );
  }

}
