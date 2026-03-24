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
