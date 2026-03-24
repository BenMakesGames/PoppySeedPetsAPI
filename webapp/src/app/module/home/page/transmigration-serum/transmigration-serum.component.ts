import {Component, OnDestroy, OnInit} from '@angular/core';
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import {ApiService} from "../../../shared/service/api.service";
import {PetSpeciesEncyclopediaSerializationGroup} from "../../../../model/pet-species-encyclopedia/pet-species-encyclopedia.serialization-group";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {AreYouSureDialog} from "../../../../dialog/are-you-sure/are-you-sure.dialog";
import {ActivatedRoute, Router} from "@angular/router";
import {Subscription} from "rxjs";
import { MatDialog } from "@angular/material/dialog";

@Component({
    templateUrl: './transmigration-serum.component.html',
    styleUrls: ['./transmigration-serum.component.scss'],
    standalone: false
})
export class TransmigrationSerumComponent implements OnInit, OnDestroy
{
  state = 'findPet';
  pet: MyPetSerializationGroup;
  loading = false;
  serumId: number;
  speciesAjax: Subscription;

  possiblePets: any;

  constructor(
    private api: ApiService, private matDialog: MatDialog, private router: Router,
    private activatedRoute: ActivatedRoute
  ) {

  }

  ngOnInit()
  {
    this.serumId = parseInt(this.activatedRoute.snapshot.paramMap.get('id'));
  }

  ngOnDestroy(): void {
    if(this.speciesAjax)
      this.speciesAjax.unsubscribe();
  }

  doCancelInject()
  {
    if(this.loading) return;

    this.pet = null;
    this.state = 'findPet';
  }

  doShowInject(pet: MyPetSerializationGroup)
  {
    if(pet === null) return;

    if(this.loading) return;

    this.loading = true;
    this.pet = pet;

    this.speciesAjax = this.api.get<PetSpeciesEncyclopediaSerializationGroup[]>('/encyclopedia/speciesByFamily/' + pet.species.family).subscribe({
      next: (r: ApiResponseModel<PetSpeciesEncyclopediaSerializationGroup[]>) => {
        this.possiblePets = r.data
          .filter(s => s.image !== pet.species.image)
          .map(species => {
            return {
              ...this.pet,
              species: {
                id: species.id,
                name: species.name,
                image: species.image,
                eggImage: species.eggImage,
                pregnancyStyle: species.pregnancyStyle
              }
            };
          })
        ;

        this.loading = false;
        this.state = 'injectPet';
      }
    });
  }

  doInject(species)
  {
    if(this.loading) return;

    AreYouSureDialog.open(
      this.matDialog,
      'You SUPER Sure?',
      this.pet.name + ' will become a ' + species.name + '! The only way to change them again would be with another Species Transmigration Serum!',
      'Let\'s do it!', 'On second thought...'
    ).afterClosed().subscribe(r => {
      if(r)
      {
        this.loading = true;

        const data = {
          pet: this.pet.id,
          species: species.id,
        };

        this.api.patch('/item/transmigrationSerum/' + this.serumId + '/INJECT', data).subscribe({
          next: () => {
            this.router.navigate([ '/home' ]);
          },
          error: () => {
            this.loading = false;
          }
        })
      }
      else
      {
      }
    })
  }

}
