import {Component, OnDestroy, OnInit} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {UserPublicProfileSerializationGroup} from "../../../../model/public-profile/user-public-profile.serialization-group";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {FilterResultsSerializationGroup} from "../../../../model/filter-results.serialization-group";
import {Subscription} from "rxjs";

@Component({
    selector: 'app-followers-list',
    templateUrl: './followers-list.component.html',
    styleUrls: ['./followers-list.component.scss'],
    standalone: false
})
export class FollowersListComponent implements OnInit, OnDestroy
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

    this.searchAjax = this.api.get<FilterResultsSerializationGroup<UserPublicProfileSerializationGroup>>('/following/followers', filter).subscribe({
      next: (r: ApiResponseModel<FilterResultsSerializationGroup<UserPublicProfileSerializationGroup>>) => {
        this.results = r.data;
      }
    })
  }

}
