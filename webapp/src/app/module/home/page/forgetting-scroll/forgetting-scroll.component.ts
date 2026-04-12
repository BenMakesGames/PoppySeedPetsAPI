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
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import {ActivatedRoute, Router} from "@angular/router";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {Subscription} from "rxjs";

@Component({
    templateUrl: './forgetting-scroll.component.html',
    styleUrls: ['./forgetting-scroll.component.scss'],
    standalone: false
})
export class ForgettingScrollComponent implements OnInit, OnDestroy {

  scrollId: number;
  state = 'findPet';
  loading = false;
  pet: MyPetSerializationGroup;
  merits: string[];
  skills: string[];
  forget = '';

  forgettableThingsAjax: Subscription;

  constructor(
    private api: ApiService, private router: Router, private activatedRoute: ActivatedRoute
  )
  {

  }

  ngOnInit()
  {
    this.scrollId = parseInt(this.activatedRoute.snapshot.paramMap.get('id'));
  }

  ngOnDestroy(): void {
    this.forgettableThingsAjax.unsubscribe();
  }

  doSelectPet(pet: MyPetSerializationGroup)
  {
    if(pet === null) return;

    if(this.loading) return;

    this.loading = true;
    this.pet = pet;

    this.forgettableThingsAjax = this.api.get<ForgettableThings>('/item/forgettingScroll/' + this.scrollId + '/forgettableThings', { pet: pet.id })
      .subscribe({
        next: (r: ApiResponseModel<ForgettableThings>) => {
          this.merits = r.data.merits;
          this.skills = r.data.skills;
          this.loading = false;
          this.state = 'forgetStuff';
        },
        error: () => {
          this.loading = false;
        }
      })
    ;
  }

  doForget()
  {
    if(this.loading) return;

    this.loading = true;

    if(this.forget.startsWith('merit:'))
    {
      const merit = this.forget.substring(6);

      this.api.post('/item/forgettingScroll/' + this.scrollId + '/forgetMerit', { pet: this.pet.id, merit: merit })
        .subscribe({
          next: () => {
            this.router.navigate([ '/home' ]);
          },
          error: () => {
            this.loading = false;
          }
        })
      ;
    }
    else if(this.forget.startsWith('skill:'))
    {
      const skill = this.forget.substring(6);

      this.api.post('/item/forgettingScroll/' + this.scrollId + '/forgetSkill', { pet: this.pet.id, skill: skill })
        .subscribe({
          next: () => {
            this.router.navigate([ '/home' ]);
          },
          error: () => {
            this.loading = false;
          }
        })
      ;
    }
  }
}

interface ForgettableThings
{
  merits: string[];
  skills: string[];
}
