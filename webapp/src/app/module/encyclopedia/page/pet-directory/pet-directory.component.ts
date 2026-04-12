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
import {FilterResultsSerializationGroup} from "../../../../model/filter-results.serialization-group";
import {ApiService} from "../../../shared/service/api.service";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {PetPublicProfileSerializationGroup} from "../../../../model/public-profile/pet-public-profile.serialization-group";
import {Subscription} from "rxjs";
import { ActivatedRoute, ParamMap, Router } from "@angular/router";
import { QueryStringService } from "../../../../service/query-string.service";
import {
  CreatePetSearchModel,
  CreatePetSearchModelFromQueryObject,
  CreateRequestDtoFromPetSearchModel, PetSearchModel
} from "../../../../model/search/pet-search-model";

@Component({
    selector: 'app-pet-directory',
    templateUrl: './pet-directory.component.html',
    styleUrls: ['./pet-directory.component.scss'],
    standalone: false
})
export class PetDirectoryComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Poppyopedia - Pets' };

  filter: PetSearchModel = CreatePetSearchModel();
  results: FilterResultsSerializationGroup<PetPublicProfileSerializationGroup>|null = null;

  petSearchAjax = Subscription.EMPTY;

  constructor(private api: ApiService, private router: Router, private activatedRoute: ActivatedRoute) { }

  ngOnInit() {
    this.activatedRoute.queryParamMap.subscribe({
      next: (p: ParamMap) =>
      {
        const params = QueryStringService.parse(p);

        let page = 0;

        const filter = 'filter' in params
          ? CreatePetSearchModelFromQueryObject(params.filter)
          : CreatePetSearchModel();

        if('page' in params)
          page = QueryStringService.parseInt(params.page, 0);

        this.runSearch(page, filter);
      }
    });
  }

  ngOnDestroy() {
    this.petSearchAjax.unsubscribe();
  }

  doChangePage(page: number)
  {
    this.runSearch(page, this.filter);
  }

  doFilter(filter: any)
  {
    const queryParams = QueryStringService.convertToAngularParams({
      page: 0,
      filter: filter,
    });

    this.results = null;

    this.router.navigate([], { queryParams: queryParams });
  }

  runSearch(page: number, filter: PetSearchModel)
  {
    this.petSearchAjax.unsubscribe();

    const data = {
      page: page,
      filter: CreateRequestDtoFromPetSearchModel(filter),
      orderBy: filter.orderBy
    };

    this.petSearchAjax = this.api.get<FilterResultsSerializationGroup<PetPublicProfileSerializationGroup>>('/pet', data).subscribe(
      (r: ApiResponseModel<FilterResultsSerializationGroup<PetPublicProfileSerializationGroup>>) => {
        this.results = r.data;
        this.filter = filter;
      }
    );
  }
}
