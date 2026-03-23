import {Component, OnInit} from '@angular/core';
import {FilterResultsSerializationGroup} from "../../../../model/filter-results.serialization-group";
import {PetGroupIndexSerializationGroup} from "../../../../model/pet-group-index.serialization-group";
import {Subscription} from "rxjs";
import {ApiService} from "../../../shared/service/api.service";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {PetGroupSearchModel} from "../../../../model/search/pet-group-search.model";

@Component({
    templateUrl: './groups.component.html',
    styleUrls: ['./groups.component.scss'],
    standalone: false
})
export class GroupsComponent implements OnInit {
  pageMeta = { title: 'Poppyopedia - Groups' };

  search: PetGroupSearchModel = { name: '', type: '', withPetsOwnedBy: null };
  results: FilterResultsSerializationGroup<PetGroupIndexSerializationGroup>|null = null;
  resultsSubscription = Subscription.EMPTY;

  constructor(
    private api: ApiService,
  ) {

  }

  ngOnInit()
  {
    this.getPage(0);
  }

  doSearch()
  {
    this.results = null;
    this.getPage(0);
  }

  getPage(page: number)
  {
    this.resultsSubscription.unsubscribe();

    const data = {
      page: page,
      filter: this.search
    };

    this.resultsSubscription = this.api.get<FilterResultsSerializationGroup<PetGroupIndexSerializationGroup>>('/petGroup', data).subscribe({
      next: (r: ApiResponseModel<FilterResultsSerializationGroup<PetGroupIndexSerializationGroup>>) => {
        this.results = r.data;
      }
    });
  }

}
