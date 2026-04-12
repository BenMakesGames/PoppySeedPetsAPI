/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, OnInit, ViewChild } from '@angular/core';
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import {ApiService} from "../../../shared/service/api.service";
import {ActivatedRoute, Router} from "@angular/router";
import {ItemActionResponseSerializationGroup} from "../../../../model/item-action-response.serialization-group";
import {ApiResponseModel} from "../../../../model/api-response.model";
import { SelectPetComponent } from "../../../shared/component/select-pet/select-pet.component";

@Component({
    templateUrl: './iridescent-hand-cannon.component.html',
    styleUrls: ['./iridescent-hand-cannon.component.scss'],
    standalone: false
})
export class IridescentHandCannonComponent implements OnInit {

  @ViewChild(SelectPetComponent) selectPet: SelectPetComponent;

  firing = false;
  itemId: number;
  selectedPet: MyPetSerializationGroup|null = null
  selectedPetHasHyperchromatic = false;
  aimAt = 'A';

  constructor(private api: ApiService, private router: Router, private activatedRoute: ActivatedRoute) { }

  ngOnInit() {
    this.itemId = parseInt(this.activatedRoute.snapshot.paramMap.get('id'));
  }

  doNothing() {}

  doSelectPet(pet: MyPetSerializationGroup|null)
  {
    this.selectedPet = pet;
    this.selectedPetHasHyperchromatic = pet?.merits.some(m => m.name === 'Hyperchromatic') ?? false;
  }

  doFire()
  {
    if(this.firing) return;

    this.firing = true;

    this.api.patch<ItemActionResponseSerializationGroup>('/item/iridescentHandCannon/' + this.itemId + '/fire', { pet: this.selectedPet.id, color: this.aimAt })
      .subscribe({
        next: (r: ApiResponseModel<ItemActionResponseSerializationGroup>) => {
          if(r.data.itemDeleted)
            this.router.navigate([ '/home' ]);
          else
          {
            this.selectPet.reload();
            this.firing = false;
          }
        },
        error: () => {
          this.firing = false;
        }
      })
  }

}
