import {Component, OnDestroy, OnInit} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {UserPublicProfileSerializationGroup} from "../../../../model/public-profile/user-public-profile.serialization-group";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {FilterResultsSerializationGroup} from "../../../../model/filter-results.serialization-group";
import {Subscription} from "rxjs";

@Component({
    selector: 'app-following-list',
    templateUrl: './following-list.component.html',
    styleUrls: ['./following-list.component.scss'],
    standalone: false
})
export class FollowingListComponent implements OnInit, OnDestroy
{
  results: FilterResultsSerializationGroup<UserPublicProfileSerializationGroup>;
  searchAjax: Subscription;

  constructor(private api: ApiService) { }

  ngOnInit() {
    this.search();
  }

  ngOnDestroy(): void {
    this.searchAjax.unsubscribe();
  }

  doChangePage(page: number)
  {
    this.search(page);
  }

  private search(page = 0)
  {
    this.results = null;

    const filter = {
      page: page
    };

    this.searchAjax = this.api.get<FilterResultsSerializationGroup<UserPublicProfileSerializationGroup>>('/following', filter).subscribe({
      next: (r: ApiResponseModel<FilterResultsSerializationGroup<UserPublicProfileSerializationGroup>>) => {
        this.results = r.data;
      }
    })
  }

}
