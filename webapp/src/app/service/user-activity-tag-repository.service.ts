/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Injectable } from '@angular/core';
import { ApiService } from "../module/shared/service/api.service";
import { Observable, of } from "rxjs";
import { ActivityLogTagSerializationGroup } from "../model/activity-log-tag.serialization-group";
import { map } from "rxjs/operators";

@Injectable({
  providedIn: 'root'
})
export class UserActivityTagRepositoryService {

  constructor(private apiService: ApiService) { }

  petActivityTags: ActivityLogTagSerializationGroup[]|null = null;

  getMatchingTags(searchText: string): Observable<ActivityLogTagSerializationGroup[]|null>
  {
    if(searchText.trim().length == 0)
      return of(null);

    if(this.petActivityTags == null)
    {
      return this.apiService.get<ActivityLogTagSerializationGroup[]>('/userActivityLogs/getAllTags').pipe(
        map(r => {
          this.petActivityTags = r.data;
          return this.filterTags(r.data, searchText);
        })
      );
    }

    return of(this.filterTags(this.petActivityTags, searchText));
  }

  private filterTags(tags: ActivityLogTagSerializationGroup[], searchText: string)
  {
    return tags
      .filter(tag =>
        tag.title.toLowerCase().includes(searchText.toLowerCase()) ||
        searchText.toLowerCase().includes(tag.title.toLowerCase())
      )
      .sort((a, b) => a.title.localeCompare(b.title))
    ;
  }
}
