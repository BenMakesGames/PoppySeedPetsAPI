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
