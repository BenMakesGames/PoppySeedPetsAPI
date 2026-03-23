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
