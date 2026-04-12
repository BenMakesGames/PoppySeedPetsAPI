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
import {ApiService} from "../../../shared/service/api.service";
import {PetShelterPetSerializationGroup} from "../../../../model/pet-shelter-pet.serialization-group";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {AreYouSureDialog} from "../../../../dialog/are-you-sure/are-you-sure.dialog";
import {UserDataService} from "../../../../service/user-data.service";
import {MyAccountSerializationGroup} from "../../../../model/my-account/my-account.serialization-group";
import {Router} from "@angular/router";
import {Subscription} from "rxjs";
import { MatDialog } from "@angular/material/dialog";
import { HasSounds, SoundsService } from "../../../shared/service/sounds.service";

@Component({
    templateUrl: './pet-shelter.component.html',
    styleUrls: ['./pet-shelter.component.scss'],
    standalone: false
})
@HasSounds([ 'chaching' ])
export class PetShelterComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Pet Shelter - Adopt a Pet' };

  user: MyAccountSerializationGroup;
  acting = false;
  shelter: PetShelterModel;

  dialogStep = 'default';
  petShelterAjax: Subscription;

  constructor(
    private api: ApiService, private matDialog: MatDialog, private userDataService: UserDataService,
    private router: Router, private sounds: SoundsService
  ) {
  }

  ngOnInit() {
    this.user = this.userDataService.user.getValue();

    this.petShelterAjax = this.api.get<PetShelterModel>('/petShelter').subscribe({
      next: (r: ApiResponseModel<PetShelterModel>) => {
        this.shelter = r.data;
      }
    })
  }

  ngOnDestroy(): void {
    this.petShelterAjax.unsubscribe();
  }

  doAdopt(pet: PetShelterPetSerializationGroup)
  {
    if(this.acting) return;

    this.acting = true;

    const dialogTitle = this.shelter.petsAtHome >= this.shelter.maxPets
      ? pet.name + ' will be placed in daycare!'
      : pet.name + ' will return home with you!'
    ;

    AreYouSureDialog.open(
      this.matDialog,
      dialogTitle,
      'Would you like to adopt ' + pet.name + ' for ' + this.shelter.costToAdopt + '~~m~~?',
      'Yes!',
      'Er, wait, let me think...'
    ).afterClosed().subscribe(
      (r) => {
        if(r) {
          this.api.post('/petShelter/' + pet.id + '/adopt', { name: pet.name }).subscribe({
            next: () => {
              this.sounds.playSound('chaching');
              this.shelter.pets = [];

              if(this.shelter.petsAtHome >= this.shelter.maxPets)
                this.router.navigate([ '/petShelter/daycare' ]);
              else
                this.shelter.dialog = 'Thanks so much! I\'m sure ' + pet.name + ' will be very happy in their new home!';

              this.acting = false;
            },
            error: () => {
              this.acting = false;
            }
          })
        }
        else
          this.acting = false;
      }
    );
  }
}

interface PetShelterModel
{
  dialog: string;
  costToAdopt: number;
  petsAtHome: number;
  maxPets: number;
  pets: PetShelterPetSerializationGroup[];
}
