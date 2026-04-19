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
import {FilterResultsSerializationGroup} from "../../../../model/filter-results.serialization-group";
import {ApiService} from "../../../shared/service/api.service";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {SpiritCompanionPublicProfileSerializationGroup} from "../../../../model/spirit-companion-public-profile.serialization-group";
import {Subscription} from "rxjs";

@Component({
    selector: 'app-spirit-companion-directory',
    templateUrl: './spirit-companion-directory.component.html',
    styleUrls: ['./spirit-companion-directory.component.scss'],
    standalone: false
})
export class SpiritCompanionDirectoryComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Poppyopedia - Spirit Companions' };

  page: number = 0;
  results: FilterResultsSerializationGroup<SpiritCompanionPublicProfileSerializationGroup>|null = null;
  searchAjax = Subscription.EMPTY;

  constructor(private api: ApiService) { }

  ngOnInit() {
    this.doSearch();
  }

  ngOnDestroy() {
    this.searchAjax.unsubscribe();
  }

  doSearch()
  {
    this.searchAjax.unsubscribe();

    const data = {
      page: this.page
    };

    this.searchAjax = this.api.get<FilterResultsSerializationGroup<SpiritCompanionPublicProfileSerializationGroup>>('/spiritCompanion/search', data).subscribe(
      (r: ApiResponseModel<FilterResultsSerializationGroup<SpiritCompanionPublicProfileSerializationGroup>>) => {
        this.results = r.data;
        this.page = r.data.page;
      }
    );
  }
}
