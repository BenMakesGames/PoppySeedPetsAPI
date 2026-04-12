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
