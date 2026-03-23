import { Component, OnDestroy, OnInit } from '@angular/core';
import { ApiService } from "../../../shared/service/api.service";
import { Subscription } from "rxjs";
import { ActivatedRoute, Router } from "@angular/router";
import { FilterResultsSerializationGroup } from "../../../../model/filter-results.serialization-group";
import { ApiResponseModel } from "../../../../model/api-response.model";

@Component({
    templateUrl: './showcase.component.html',
    styleUrls: ['./showcase.component.scss'],
    standalone: false
})
export class ShowcaseComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Achievements - Showcase' };

  pageSubscription = Subscription.EMPTY;
  page: number = 0;

  showcasePage: FilterResultsSerializationGroup<Achiever>|null = null;

  constructor(
    private api: ApiService, private activatedRoute: ActivatedRoute, private router: Router
  )
  {
  }

  ngOnInit() {
    this.activatedRoute.queryParams.subscribe(q => {
      this.page = q.page ?? 0;

      this.loadPage();
    });
  }

  public loadPage()
  {
    const data = {
      page: this.page
    };

    this.pageSubscription = this.api.get<FilterResultsSerializationGroup<Achiever>>('/achievement/showcase', data).subscribe({
      next: (r: ApiResponseModel<FilterResultsSerializationGroup<Achiever>>) => {
        this.showcasePage = r.data;
      }
    });
  }

  ngOnDestroy() {
    this.pageSubscription.unsubscribe();
  }

  doViewPlayer(id: number)
  {
    this.router.navigateByUrl('/poppyopedia/resident/' + id);
  }
}

interface Achiever
{
  resident: { id: number, name: string, icon: string };
  achievementCount: number;
}