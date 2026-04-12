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
