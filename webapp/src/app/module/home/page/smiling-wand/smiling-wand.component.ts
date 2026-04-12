/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, OnInit} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import {ActivatedRoute, Router} from "@angular/router";
import {Subscription} from "rxjs";
import { EmoteFontAwesomeClasses } from "../../../../model/emote-font-awesome-classes";

@Component({
    templateUrl: './smiling-wand.component.html',
    styleUrls: ['./smiling-wand.component.scss'],
    standalone: false
})
export class SmilingWandComponent implements OnInit {

  wandId: number;
  state = 'findPet';
  pet: MyPetSerializationGroup;
  iconsSelected: string[] = [];

  waveWandAjax = Subscription.EMPTY;

  readonly letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');

  constructor(
    private api: ApiService, private router: Router, private activatedRoute: ActivatedRoute
  )
  {

  }

  ngOnInit()
  {
    this.wandId = parseInt(this.activatedRoute.snapshot.paramMap.get('id'));
  }

  doToggleIcon(letter: string)
  {
    const letterIndex = this.iconsSelected.indexOf(letter);

    if(letterIndex == -1)
    {
      if(this.iconsSelected.length < 3)
        this.iconsSelected = [ ...this.iconsSelected, letter ];
      else
        this.iconsSelected = [ ...this.iconsSelected.slice(1), letter ];
    }
    else
    {
      this.iconsSelected = this.iconsSelected.filter(l => l != letter);
    }
  }

  doSelectPet(pet: MyPetSerializationGroup)
  {
    if(!this.waveWandAjax.closed) return;

    this.pet = pet;
    this.state = this.pet == null ? 'findPet' : 'changeExpressions';
  }

  doSubmit()
  {
    if(!this.waveWandAjax.closed) return;

    if(this.iconsSelected.length != 3)
      return;

    this.waveWandAjax = this.api.post('/item/smilingWand/' + this.wandId + '/use', { pet: this.pet.id, expressions: this.iconsSelected.join('') })
      .subscribe({
        next: () => {
          this.router.navigate([ '/home' ]);
        },
      })
    ;
  }

  protected readonly emoteFontAwesomeClasses = EmoteFontAwesomeClasses;
}
