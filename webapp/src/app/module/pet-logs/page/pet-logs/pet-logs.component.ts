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
import {Subscription} from "rxjs";
import {ApiService} from "../../../shared/service/api.service";
import {FilterResultsSerializationGroup} from "../../../../model/filter-results.serialization-group";
import { ActivatedRoute, ParamMap, Router } from "@angular/router";
import {QueryStringService} from "../../../../service/query-string.service";
import { ActivityLogTagSerializationGroup } from "../../../../model/activity-log-tag.serialization-group";
import { ReenactmentDialog } from "../../dialogs/reenactment/reenactment.dialog";
import { MatDialog } from "@angular/material/dialog";
import { MyAccountSerializationGroup } from "../../../../model/my-account/my-account.serialization-group";
import { UserDataService } from "../../../../service/user-data.service";
import { PetActivityTagRepositoryService } from "../../../../service/pet-activity-tag-repository.service";

@Component({
    templateUrl: './pet-logs.component.html',
    styleUrls: ['./pet-logs.component.scss'],
    standalone: false
})
export class PetLogsComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Journal - Pet Activity' };

  searchSubscription = Subscription.EMPTY;
  userSubscription = Subscription.EMPTY;

  user: MyAccountSerializationGroup;

  results: FilterResultsSerializationGroup<any>|undefined;

  page: number = 0;
  filter: any = { };
  queryParams: any;

  tags: ActivityLogTagSerializationGroup[] = [];

  constructor(
    private api: ApiService, private activatedRoute: ActivatedRoute,
    private router: Router, private matDialog: MatDialog,
    private userData: UserDataService,
    private activityTagRepository: PetActivityTagRepositoryService
  ) { }

  ngOnInit(): void {

    this.userSubscription = this.userData.user.subscribe(u => { this.user = u; });

    this.activatedRoute.queryParamMap.subscribe({
      next: (p: ParamMap) =>
      {
        const params = QueryStringService.parse(p);

        if('filter' in params)
          this.filter = this.buildSearchModelFromQueryObject(params.filter);
        else
          this.filter = {};

        this.queryParams = QueryStringService.convertToAngularParams({
          filter: this.filter
        });

        if('page' in params)
          this.page = QueryStringService.parseInt(params.page, 0);
        else
          this.page = 0;

        this.runSearch();
      }
    });
  }

  doViewEvent(log: any)
  {
    ReenactmentDialog.open(
      this.matDialog,
      [
        { pet: log.pet, tool: log.equippedItem, tags: log.tags, createdItems: log.createdItems, caption: log.entry }
      ],
      0
    );
  }

  ngOnDestroy(): void {
    this.userSubscription.unsubscribe();
    this.searchSubscription.unsubscribe();
  }

  doFilter()
  {
    this.page = 0;

    this.filter.tags = this.tags.map(t => t.title);

    this.queryParams = QueryStringService.convertToAngularParams({
      page: this.page,
      filter: this.filter
    });

    this.router.navigate([], { queryParams: this.queryParams });
  }

  doChangePage(page: number)
  {
    this.page = page;

    this.runSearch();
  }

  runSearch()
  {
    this.searchSubscription.unsubscribe();

    const data = {
      page: this.page,
      filter: this.filter
    };

    this.searchSubscription = this.api.get<FilterResultsSerializationGroup<any>>('/petActivityLogs', data).subscribe({
      next: r => {
        this.results = r.data;
      }
    });
  }

  public buildSearchModelFromQueryObject(query: any): any
  {
    let search: any = { };

    if('tags' in query) search.tags = QueryStringService.parseArray(query.tags);

    this.activityTagRepository.getMatchingTags('dummy text').subscribe(tags => {
      this.tags = search.tags.map(t => this.activityTagRepository.petActivityTags.find(tag => tag.title === t));
    });

    return search;
  }
}
