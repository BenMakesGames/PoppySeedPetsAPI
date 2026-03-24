import {Component, OnDestroy, OnInit} from '@angular/core';
import {FilterResultsSerializationGroup} from "../../../../model/filter-results.serialization-group";
import {UserPublicProfileSerializationGroup} from "../../../../model/public-profile/user-public-profile.serialization-group";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {ApiService} from "../../../shared/service/api.service";
import {UserDataService} from "../../../../service/user-data.service";
import {Subscription} from "rxjs";
import { ActivatedRoute, ParamMap, Router } from "@angular/router";
import {QueryStringService} from "../../../../service/query-string.service";
import {
  CreatePlayerSearchModel,
  CreatePlayerSearchModelFromQueryObject, CreateRequestDtoFromPlayerSearchModel,
  PlayerSearchModel
} from "../../../../model/search/player-search-model";

@Component({
    templateUrl: './resident-directory.component.html',
    styleUrls: ['./resident-directory.component.scss'],
    standalone: false
})
export class ResidentDirectoryComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Poppyopedia - Residents' };

  filter: PlayerSearchModel = CreatePlayerSearchModel();

  loggedIn = false;
  userSubscription = Subscription.EMPTY;
  page: number = 0;
  results: FilterResultsSerializationGroup<UserPublicProfileSerializationGroup>|null = null;
  searchAjax = Subscription.EMPTY;

  constructor(
    private api: ApiService, private userData: UserDataService, private activatedRoute: ActivatedRoute,
    private router: Router
  ) { }

  ngOnInit() {
    this.userSubscription = this.userData.user.subscribe({
      next: (u) => {
        this.loggedIn = !!u;
      }
    });

    this.activatedRoute.queryParamMap.subscribe({
      next: (p: ParamMap) =>
      {
        const params = QueryStringService.parse(p);

        let page = 0;

        const filter = 'filter' in params
          ? CreatePlayerSearchModelFromQueryObject(params.filter)
          : CreatePlayerSearchModel();

        if('page' in params)
          page = QueryStringService.parseInt(params.page, 0);

        this.doSearch(page, filter);
      }
    });
  }

  doFilter(filter: any)
  {
    const queryParams = QueryStringService.convertToAngularParams({
      page: 0,
      filter: filter
    });

    this.results = null;

    this.router.navigate([], { queryParams: queryParams });
  }

  ngOnDestroy() {
    this.userSubscription.unsubscribe();
    this.searchAjax.unsubscribe();
  }

  doSearch(page: number, filter: PlayerSearchModel)
  {
    this.searchAjax.unsubscribe();

    const data = {
      page: page,
      filter: CreateRequestDtoFromPlayerSearchModel(filter)
    };

    this.searchAjax = this.api.get<FilterResultsSerializationGroup<UserPublicProfileSerializationGroup>>('/account/search', data).subscribe(
      (r: ApiResponseModel<FilterResultsSerializationGroup<UserPublicProfileSerializationGroup>>) => {
        this.results = r.data;
        this.page = r.data.page;
      }
    );
  }
}
