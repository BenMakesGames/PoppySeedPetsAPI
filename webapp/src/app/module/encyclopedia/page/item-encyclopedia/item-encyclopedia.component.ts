import {Component, OnDestroy, OnInit} from '@angular/core';
import {FilterResultsSerializationGroup} from "../../../../model/filter-results.serialization-group";
import {ItemEncyclopediaSerializationGroup} from "../../../../model/encyclopedia/item-encyclopedia.serialization-group";
import {ApiService} from "../../../shared/service/api.service";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {
  CreateItemSearchModel,
  CreateItemSearchModelFromQueryObject, CreateRequestDtoFromItemSearchModel,
  ItemSearchModel
} from "../../../../model/search/item-search.model";
import {ActivatedRoute, ParamMap, Router} from "@angular/router";
import {QueryStringService} from "../../../../service/query-string.service";
import {Subscription} from "rxjs";
import {UserDataService} from "../../../../service/user-data.service";
import {MyAccountSerializationGroup} from "../../../../model/my-account/my-account.serialization-group";

@Component({
    templateUrl: './item-encyclopedia.component.html',
    styleUrls: ['./item-encyclopedia.component.scss'],
    standalone: false
})
export class ItemEncyclopediaComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Poppyopedia - Items' };

  page: number = 0;
  results: FilterResultsSerializationGroup<ItemEncyclopediaSerializationGroup>|null = null;
  search: ItemSearchModel = CreateItemSearchModel();
  searchAjax = Subscription.EMPTY;
  userSubscription = Subscription.EMPTY;
  user: MyAccountSerializationGroup;
  queryParams: any;

  constructor(
    private api: ApiService, private activatedRoute: ActivatedRoute, private router: Router,
    private userDataService: UserDataService
  ) {
  }

  ngOnInit() {
    this.userSubscription = this.userDataService.user.subscribe({
      next: u => { this.user = u; }
    });

    this.activatedRoute.queryParamMap.subscribe({
      next: (p: ParamMap) =>
      {
        const params = QueryStringService.parse(p);

        if('filter' in params)
          this.search = CreateItemSearchModelFromQueryObject(params.filter);
        else
          this.search = CreateItemSearchModel();

        this.queryParams = QueryStringService.convertToAngularParams({
          filter: this.search
        });

        if('page' in params)
          this.page = QueryStringService.parseInt(params.page, 0);
        else
          this.page = 0;

        this.runSearch();
      }
    });
  }

  ngOnDestroy(): void {
    this.searchAjax.unsubscribe();
    this.userSubscription.unsubscribe();
  }

  doSearch(search: any)
  {
    const newQueryParams = QueryStringService.convertToAngularParams({
      filter: search
    });

    if(JSON.stringify(newQueryParams) == JSON.stringify(this.queryParams))
      return;

    this.results = null;

    this.router.navigate([], { queryParams: { page: 0, ... newQueryParams } });
  }

  runSearch()
  {
    this.searchAjax.unsubscribe();

    let data: { page: number, filter: any } = {
      page: this.page,
      filter: CreateRequestDtoFromItemSearchModel(this.search)
    };

    this.searchAjax = this.api.get<FilterResultsSerializationGroup<ItemEncyclopediaSerializationGroup>>('/encyclopedia/item', data).subscribe(
      (r: ApiResponseModel<FilterResultsSerializationGroup<ItemEncyclopediaSerializationGroup>>) => {
        this.results = r.data;
        this.page = r.data.page;
      }
    );
  }
}
