import {Component, OnDestroy, OnInit} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {MeritEncyclopediaSerializationGroup} from "../../../../model/encyclopedia/merit-encyclopedia.serialization-group";
import {FilterResultsSerializationGroup} from "../../../../model/filter-results.serialization-group";
import {Subscription} from "rxjs";
import { ActivatedRoute, ParamMap } from "@angular/router";
import { QueryStringService } from "../../../../service/query-string.service";

@Component({
    templateUrl: './merit.component.html',
    styleUrls: ['./merit.component.scss'],
    standalone: false
})
export class MeritComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Poppyopedia - Merits' };

  results: FilterResultsSerializationGroup<MeritEncyclopediaSerializationGroup>;
  loading = false;

  meritAjax: Subscription;
  page = 0;

  constructor(private api: ApiService, private activatedRoute: ActivatedRoute) {
  }

  ngOnInit() {
    this.activatedRoute.queryParamMap.subscribe({
      next: (p: ParamMap) =>
      {
        const params = QueryStringService.parse(p);

        if('page' in params)
          this.page = QueryStringService.parseInt(params.page, 0);
        else
          this.page = 0;

        this.doSearch();
      }
    });
  }

  ngOnDestroy(): void {
    this.meritAjax.unsubscribe();
  }

  doSearch()
  {
    if(this.loading) return;

    this.loading = true;

    this.meritAjax = this.api.get<FilterResultsSerializationGroup<MeritEncyclopediaSerializationGroup>>('/encyclopedia/merit', { page: this.page }).subscribe(
      r => {
        this.results = r.data;

        this.results.results = this.results.results.map(m => {
          return {
            name: m.name,
            description: this.formatDescription(m.description)
          }
        });

        this.loading = false;
      }
    );
  }

  private formatDescription(description: string)
  {
    let formatted = description.replace(/%pet\.name%/g, 'this pet');
    return formatted[0].toUpperCase() + formatted.substr(1);
  }
}
