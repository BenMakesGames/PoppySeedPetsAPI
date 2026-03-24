import { Component, OnInit } from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {ActivatedRoute, Router} from "@angular/router";
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";

@Component({
    templateUrl: './polymorph-spirit-scroll.component.html',
    styleUrls: ['./polymorph-spirit-scroll.component.scss'],
    standalone: false
})
export class PolymorphSpiritScrollComponent implements OnInit {

  polymorphing = false;
  potionId: number;

  constructor(private api: ApiService, private router: Router, private activatedRoute: ActivatedRoute)
  {

  }

  ngOnInit()
  {
    this.potionId = parseInt(this.activatedRoute.snapshot.paramMap.get('id'));
  }

  doPolymorph(pet: MyPetSerializationGroup)
  {
    if(pet === null) return;

    if(this.polymorphing) return;

    this.polymorphing = true;

    this.api.patch('/item/spiritPolymorphPotion/' + this.potionId + '/drink', { pet: pet.id })
      .subscribe({
        next: () => {
          this.router.navigate([ '/home' ]);
        },
        error: () => {
          this.polymorphing = false;
        }
      })
  }
}
