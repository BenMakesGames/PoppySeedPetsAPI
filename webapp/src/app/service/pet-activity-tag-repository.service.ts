import { Injectable } from '@angular/core';
import { ApiService } from "../module/shared/service/api.service";
import { Observable, of } from "rxjs";
import { ActivityLogTagSerializationGroup } from "../model/activity-log-tag.serialization-group";
import { map } from "rxjs/operators";

@Injectable({
  providedIn: 'root'
})
export class PetActivityTagRepositoryService {

  constructor(private apiService: ApiService) { }

  petActivityTags: ActivityLogTagSerializationGroup[]|null = null;

  getMatchingTags(searchText: string): Observable<ActivityLogTagSerializationGroup[]|null>
  {
    if(searchText.trim().length == 0)
      return of(null);

    if(this.petActivityTags == null)
    {
      return this.apiService.get<ActivityLogTagSerializationGroup[]>('/petActivityLogs/getAllTags').pipe(
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
