import {Component, OnDestroy, OnInit} from '@angular/core';
import { ActivatedRoute, Router } from "@angular/router";
import {PetPublicProfileSerializationGroup} from "../../../../model/public-profile/pet-public-profile.serialization-group";
import {FilterResultsSerializationGroup} from "../../../../model/filter-results.serialization-group";
import {PetActivitySerializationGroup} from "../../../../model/pet-activity-logs/pet-activity.serialization-group";
import {ApiService} from "../../../shared/service/api.service";
import {UserDataService} from "../../../../service/user-data.service";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {DonutChartDataPointModel} from "../../../../model/charts/donut-chart-data-point.model";
import {ChartModel} from "../../../../model/charts/chart.model";
import {FamilyTreeSerializationGroup} from "../../../../model/family-tree.serialization-group";
import {Subscription} from "rxjs";
import { MatDialog } from "@angular/material/dialog";

@Component({
    templateUrl: './pet-profile.component.html',
    styleUrls: ['./pet-profile.component.scss'],
    standalone: false
})
export class PetProfileComponent implements OnInit, OnDestroy {

  tabs: any[] = [];

  logsView = 'table';
  tab: string;
  showLogs = false;
  viewedRelationshipTab = false;
  viewedActivityCalendar = false;
  pet: PetPublicProfileSerializationGroup|null = null;
  logs: FilterResultsSerializationGroup<PetActivitySerializationGroup>;
  familyTree: FamilyTreeSerializationGroup;
  activityStatsCharts: ChartModel<DonutChartDataPointModel>[];
  loadingLogs = false;
  loadingFamilyTree = false;
  petAjax = Subscription.EMPTY;
  familyTreeAjax = Subscription.EMPTY;
  activityStatsAjax = Subscription.EMPTY;
  petLogsAjax = Subscription.EMPTY;

  constructor(
    private activatedRoute: ActivatedRoute, private api: ApiService, private userData: UserDataService,
    private matDialog: MatDialog, private router: Router
  ) {

  }

  ngOnInit() {
    // no need to unsubscribe from paramMap, apparently
    this.activatedRoute.paramMap.subscribe(params => {
      const petId = parseInt(params.get('pet'));
      if(!this.pet || this.pet.id != petId) {
        this.petAjax = this.api.get<PetPublicProfileSerializationGroup>('/pet/' + params.get('pet')).subscribe({
          next: (r: ApiResponseModel<PetPublicProfileSerializationGroup>) => {
            this.pet = r.data;
            this.logs = null;
            this.familyTree = null;
            this.activityStatsCharts = null;
            this.viewedRelationshipTab = false;
            this.viewedActivityCalendar = false;

            this.buildTabs();

            if(this.userData.user.value && this.pet.owner.id === this.userData.user.value.id)
            {
              this.doChangeTab(params.get('tab') ?? 'logs');
              this.showLogs = true;
              this.loadPetActivityStats();
            }
            else
            {
              this.doChangeTab(params.get('tab') ?? 'familyTree');
            }
          },
          error: _ => {
            this.router.navigateByUrl('/poppyopedia/pet', {
              replaceUrl: true
            });
          }
        });
      }
    });
  }

  ngOnDestroy(): void {
    this.petAjax.unsubscribe();
    this.familyTreeAjax.unsubscribe();
    this.activityStatsAjax.unsubscribe();
    this.petLogsAjax.unsubscribe();
  }

  private buildTabs()
  {
    this.tabs = [];

    if(this.userData.user.value && this.pet.owner.id === this.userData.user.value.id)
    {
      this.tabs.push({
        key: 'logs',
        label: 'Logs',
      });
    }

    this.tabs.push(
      {
        key: 'relationships',
        label: 'Relationships',
      },
      {
        key: 'groups',
        label: 'Groups',
      },
      {
        key: 'familyTree',
        label: 'Family Tree',
      }
    );
  }

  doChangeTab(tab: string)
  {
    this.tab = tab;

    if(this.tab === 'logs')
    {
      if(!this.logs && !this.loadingLogs)
      {
        this.loadPetLogs(-1);
      }
    }
    else if(this.tab === 'familyTree')
    {
      if(!this.familyTree && !this.loadingFamilyTree)
      {
        this.loadFamilyTree();
      }
    }
    else if(this.tab === 'relationships')
    {
      this.viewedRelationshipTab = true;
    }
  }

  loadFamilyTree()
  {
    if(this.loadingFamilyTree) return;

    this.loadingFamilyTree = true;

    this.familyTreeAjax = this.api.get<FamilyTreeSerializationGroup>('/pet/' + this.pet.id + '/familyTree').subscribe({
      next: (r: ApiResponseModel<FamilyTreeSerializationGroup>) => {
        this.familyTree = r.data;
        this.loadingFamilyTree = false;
      },
      error: () => {
        this.loadingFamilyTree = false;
      }
    });
  }

  loadPetActivityStats()
  {
    this.activityStatsAjax = this.api.get<ChartModel<DonutChartDataPointModel>[]>('/pet/' + this.pet.id + '/activityStats').subscribe({
      next: (r: ApiResponseModel<ChartModel<DonutChartDataPointModel>[]>) => {
        if(r && r.data)
          this.activityStatsCharts = r.data;
      }
    });
  }

  loadPetLogs(page: number)
  {
    if(this.loadingLogs) return;

    this.loadingLogs = true;

    this.petLogsAjax = this.api.get<FilterResultsSerializationGroup<PetActivitySerializationGroup>>('/pet/' + this.pet.id + '/logs', { page: page, orderDir: 'reverse' }).subscribe({
      next: (r: ApiResponseModel<FilterResultsSerializationGroup<PetActivitySerializationGroup>>) => {
        this.logs = r.data;
        this.loadingLogs = false;
      },
      error: () => {
        this.loadingLogs = false;
      }
    });
  }

  doChangeLogView()
  {
    if(this.logsView === 'calendar')
      this.viewedActivityCalendar = true;
  }
}
