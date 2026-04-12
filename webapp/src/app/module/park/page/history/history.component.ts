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
import {ParkEventSerializationGroup} from "../../../../model/park/park-event.serialization-group";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {FilterResultsSerializationGroup} from "../../../../model/filter-results.serialization-group";
import {ParkEventDetailsDialog} from "../../dialog/park-event-details/park-event-details.dialog";
import {MyAccountSerializationGroup} from "../../../../model/my-account/my-account.serialization-group";
import {UserDataService} from "../../../../service/user-data.service";
import {Subscription} from "rxjs";
import {ActivatedRoute} from "@angular/router";
import { MatDialog } from "@angular/material/dialog";

@Component({
    selector: 'app-history',
    templateUrl: './history.component.html',
    styleUrls: ['./history.component.scss'],
    standalone: false
})
export class HistoryComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Park - History', song: 'the-ocean' };

  page: number;
  user: MyAccountSerializationGroup;
  history: FilterResultsSerializationGroup<ParkEventSerializationGroup>;

  parkHistoryAjax = Subscription.EMPTY;

  constructor(
    private api: ApiService, private matDialog: MatDialog, private userData: UserDataService,
    private activatedRoute: ActivatedRoute
  ) { }

  ngOnInit() {
    this.user = this.userData.user.getValue();

    this.activatedRoute.queryParams.subscribe(q => {
      this.page = q.page ?? 0;

      this.loadPage();
    });
  }

  ngOnDestroy(): void {
    this.parkHistoryAjax.unsubscribe();
  }

  private loadPage()
  {
    const data = {
      page: this.page
    };

    this.parkHistoryAjax = this.api.get<FilterResultsSerializationGroup<ParkEventSerializationGroup>>('/park/history', data).subscribe({
      next: (r: ApiResponseModel<FilterResultsSerializationGroup<ParkEventSerializationGroup>>) => {
        this.history = r.data;
      }
    });
  }

  doShowEvent(event: ParkEventSerializationGroup)
  {
    ParkEventDetailsDialog.open(this.matDialog, event);
  }
}
