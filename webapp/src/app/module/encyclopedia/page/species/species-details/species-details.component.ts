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
import {PetSpeciesEncyclopediaSerializationGroup} from "../../../../../model/pet-species-encyclopedia/pet-species-encyclopedia.serialization-group";
import {ApiService} from "../../../../shared/service/api.service";
import {ApiResponseModel} from "../../../../../model/api-response.model";
import {Title} from "@angular/platform-browser";
import {Subscription} from "rxjs";

@Component({
    templateUrl: './species-details.component.html',
    styleUrls: ['./species-details.component.scss'],
    standalone: false
})
export class SpeciesDetailsComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Poppyopedia - Species' };

  loading = true;
  speciesName: string;
  species: PetSpeciesEncyclopediaSerializationGroup;

  speciesAjax: Subscription;

  constructor(
    private activatedRoute: ActivatedRoute, private api: ApiService, private titleService: Title
  ) {

  }

  ngOnInit() {
    // no need to unsubscribe from paramMap, apparently
    this.activatedRoute.paramMap.subscribe(params => {
      this.speciesName = params.get('name');

      this.speciesAjax = this.api.get<PetSpeciesEncyclopediaSerializationGroup>('/encyclopedia/species/' + encodeURIComponent(this.speciesName)).subscribe({
        next: (r: ApiResponseModel<PetSpeciesEncyclopediaSerializationGroup>) => {
          this.species = r.data;
          this.loading = false;
          this.titleService.setTitle('Poppy Seed Pets - Poppyopedia - Species - ' + this.species.name);
        },
        error: () => {
          this.loading = false;
        }
      });
    });
  }

  ngOnDestroy(): void {
    if(this.speciesAjax)
      this.speciesAjax.unsubscribe();
  }
}
