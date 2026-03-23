import {Component, OnDestroy, OnInit} from '@angular/core';
import {FilterResultsSerializationGroup} from "../../../../model/filter-results.serialization-group";
import {PetSpeciesEncyclopediaSerializationGroup} from "../../../../model/pet-species-encyclopedia/pet-species-encyclopedia.serialization-group";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {ApiService} from "../../../shared/service/api.service";
import {Subscription} from "rxjs";
import { UserDataService } from "../../../../service/user-data.service";
import { MyAccountSerializationGroup } from "../../../../model/my-account/my-account.serialization-group";
import { ActivatedRoute, ParamMap, Router } from "@angular/router";
import { QueryStringService } from "../../../../service/query-string.service";

@Component({
    templateUrl: './species.component.html',
    styleUrls: ['./species.component.scss'],
    standalone: false
})
export class SpeciesComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Poppyopedia - Species' };

  search = { ...EmptySearch };

  user: MyAccountSerializationGroup;
  userSubscription = Subscription.EMPTY;
  page: number = 0;
  resultsSubscription = Subscription.EMPTY;
  results: FilterResultsSerializationGroup<PetSpeciesEncyclopediaSerializationGroup>|null = null;
  queryParams: any = {};

  constructor(
    private api: ApiService, private userData: UserDataService, private activatedRoute: ActivatedRoute,
    private router: Router
  ) {

  }

  ngOnInit() {
    this.userSubscription = this.userData.user.subscribe({
      next: u => this.user = u
    });

    this.activatedRoute.queryParamMap.subscribe({
      next: (p: ParamMap) =>
      {
        const params = QueryStringService.parse(p);

        if('filter' in params)
        {
          if('name' in params.filter) this.search.name = params.filter.name.toString().trim();
          if('hasPet' in params.filter) this.search.hasPet = QueryStringService.parseBool(params.filter.hasPet, false);
        }
        else
        {
          this.search = { ... EmptySearch };
        }

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
    this.resultsSubscription.unsubscribe();
    this.userSubscription.unsubscribe();
  }

  doSearch()
  {
    this.page = 0;

    this.queryParams = QueryStringService.convertToAngularParams({
      page: this.page,
      filter: this.search
    });

    this.router.navigate([], { queryParams: this.queryParams });
  }

  runSearch()
  {
    this.resultsSubscription.unsubscribe();

    const data = {
      page: this.page,
      filter: this.search
    };

    this.resultsSubscription = this.api.get<FilterResultsSerializationGroup<PetSpeciesEncyclopediaSerializationGroup>>('/encyclopedia/species', data).subscribe(
      (r: ApiResponseModel<FilterResultsSerializationGroup<PetSpeciesEncyclopediaSerializationGroup>>) => {
        this.results = r.data;
        this.page = r.data.page;
      }
    );
  }

}

const EmptySearch = {
  name: '',
  hasPet: null,
  hasDiscovered: null,
}