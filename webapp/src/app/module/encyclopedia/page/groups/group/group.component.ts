import {Component, OnDestroy, OnInit} from '@angular/core';
import {ActivatedRoute} from "@angular/router";
import {ApiService} from "../../../../shared/service/api.service";
import {PetGroupDetailsSerializationGroup} from "../../../../../model/pet-group-details.serialization-group";
import {ApiResponseModel} from "../../../../../model/api-response.model";
import {Subscription} from "rxjs";

@Component({
    templateUrl: './group.component.html',
    styleUrls: ['./group.component.scss'],
    standalone: false
})
export class GroupComponent implements OnInit, OnDestroy {

  groupId: string;
  loading = true;
  group: PetGroupDetailsSerializationGroup;
  petGroupAjax: Subscription;

  constructor(private activatedRoute: ActivatedRoute, private api: ApiService) {

  }

  ngOnInit() {
    // no need to unsubscribe from paramMap, apparently
    this.activatedRoute.paramMap.subscribe(params => {
      this.groupId = params.get('group');

      this.loadGroup();
    });
  }

  ngOnDestroy(): void {
    this.petGroupAjax.unsubscribe();
  }

  private loadGroup()
  {
    this.petGroupAjax = this.api.get<PetGroupDetailsSerializationGroup>('/petGroup/' + this.groupId).subscribe({
      next: (r: ApiResponseModel<PetGroupDetailsSerializationGroup>) => {
        this.group = r.data;
        this.loading = false;
      },
      error: () => {
        this.loading = false;
      }
    })
  }

}
