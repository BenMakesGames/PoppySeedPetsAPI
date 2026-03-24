import {Component, OnDestroy, OnInit} from '@angular/core';
import {ActivatedRoute} from "@angular/router";
import {UserPublicProfileSerializationGroup} from "../../../../../model/public-profile/user-public-profile.serialization-group";
import {FilterResultsSerializationGroup} from "../../../../../model/filter-results.serialization-group";
import {MuseumSerializationGroup} from "../../../../../model/museum.serialization-group";
import {ApiService} from "../../../../shared/service/api.service";
import {ApiResponseModel} from "../../../../../model/api-response.model";
import {Subscription} from "rxjs";

@Component({
    selector: 'app-museum-wing',
    templateUrl: './museum-wing.component.html',
    styleUrls: ['./museum-wing.component.scss'],
    standalone: false
})
export class MuseumWingComponent implements OnInit, OnDestroy {

  profileId = '';
  profile: UserPublicProfileSerializationGroup;

  page: number = 0;
  results: FilterResultsSerializationGroup<MuseumSerializationGroup>;

  museumPageAjax = Subscription.EMPTY;
  profileAjax = Subscription.EMPTY;

  constructor(
    private activatedRoute: ActivatedRoute, private api: ApiService
  ) {
    
  }

  ngOnInit() {
    // no need to unsubscribe from paramMap, apparently
    this.activatedRoute.paramMap.subscribe(params => {
      this.profileId = params.get('user');

      this.loadProfile();
      this.getPage();
    });
  }

  ngOnDestroy(): void {
    this.museumPageAjax.unsubscribe();
    this.profileAjax.unsubscribe();
  }

  getPage()
  {
    this.museumPageAjax.unsubscribe();

    this.museumPageAjax = this.api.get<FilterResultsSerializationGroup<MuseumSerializationGroup>>('/museum/' + this.profileId + '/items', { page: this.page }).subscribe({
      next: (r: ApiResponseModel<FilterResultsSerializationGroup<MuseumSerializationGroup>>) => {
        this.results = r.data;
      }
    });
  }

  loadProfile()
  {
    this.profileAjax = this.api.get<UserPublicProfileSerializationGroup>('/account/' + this.profileId).subscribe({
      next: (r: ApiResponseModel<any>) => {
        this.profile = r.data.user;
      },
      error: () => {
      }
    });
  }

}
