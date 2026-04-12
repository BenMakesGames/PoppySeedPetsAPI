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
import {FilterResultsSerializationGroup} from "../../../../../model/filter-results.serialization-group";
import {MuseumDonorSerializationGroup} from "../../../../../model/museum-donor.serialization-group";
import {ApiService} from "../../../../shared/service/api.service";
import {ApiResponseModel} from "../../../../../model/api-response.model";
import {Subscription} from "rxjs";
import { Router } from "@angular/router";
import { UserDataService } from "../../../../../service/user-data.service";
import { MyAccountSerializationGroup } from "../../../../../model/my-account/my-account.serialization-group";

@Component({
    selector: 'app-museum-top-donators',
    templateUrl: './museum-top-donators.component.html',
    styleUrls: ['./museum-top-donators.component.scss'],
    standalone: false
})
export class MuseumTopDonatorsComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Museum - Top Donors' };

  page: number = 0;
  results: FilterResultsSerializationGroup<MuseumDonorSerializationGroup>|null = null;
  topDonorsAjax = Subscription.EMPTY;
  user: MyAccountSerializationGroup;

  constructor(private api: ApiService, private router: Router, private userData: UserDataService) { }

  ngOnInit()
  {
    this.getPage();

    this.user = this.userData.user.value;
  }

  ngOnDestroy(): void {
    this.topDonorsAjax.unsubscribe();
  }

  doVisitWing(userId: number)
  {
    this.router.navigateByUrl('/museum/' + userId);
  }

  getPage() {
    this.topDonorsAjax.unsubscribe();

    this.topDonorsAjax = this.api.get<FilterResultsSerializationGroup<MuseumDonorSerializationGroup>>('/museum/topDonors', { page: this.page })
      .subscribe({
        next: (r: ApiResponseModel<FilterResultsSerializationGroup<MuseumDonorSerializationGroup>>) => {
          this.results = r.data;
        }
      })
    ;
  }
}
