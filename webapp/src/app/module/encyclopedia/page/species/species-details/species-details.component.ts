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
