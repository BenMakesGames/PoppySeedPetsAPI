/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
