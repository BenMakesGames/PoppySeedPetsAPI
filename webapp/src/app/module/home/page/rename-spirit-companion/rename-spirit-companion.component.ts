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
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import {ApiService} from "../../../shared/service/api.service";
import {ActivatedRoute, Router} from "@angular/router";

@Component({
    templateUrl: './rename-spirit-companion.component.html',
    styleUrls: ['./rename-spirit-companion.component.scss'],
    standalone: false
})
export class RenameSpiritCompanionComponent implements OnInit {

  scrollId: number;
  state = 'findPet';
  pet: MyPetSerializationGroup;
  newName = '';
  renaming = false;

  constructor(private api: ApiService, private router: Router, private activatedRoute: ActivatedRoute)
  {

  }

  ngOnInit()
  {
    this.scrollId = parseInt(this.activatedRoute.snapshot.paramMap.get('id'));
  }

  disableCondition = p => !p.spiritCompanion;

  doShowRename(pet)
  {
    if(!pet) return;
    if(!pet.spiritCompanion) return;

    this.pet = pet;
    this.state = 'renamePet';
  }

  doCancelRename()
  {
    if(this.renaming) return;

    this.state = 'findPet';
  }

  doRename()
  {
    if(this.renaming) return;

    this.newName = this.newName.trim();

    if(this.newName === this.pet.name)
      return;

    this.renaming = true;

    const data = {
      pet: this.pet.id,
      name: this.newName
    };

    this.api.patch('/item/renamingScroll/' + this.scrollId + '/readToSpiritCompanion', data)
      .subscribe({
        next: () => {
          this.router.navigate([ '/home' ]);
        },
        error: () => {
          this.renaming = false;
        }
      })
  }

}
