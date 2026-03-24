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
