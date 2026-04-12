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
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import {ApiService} from "../../../shared/service/api.service";
import {ActivatedRoute, Router} from "@angular/router";
import {Subscription} from "rxjs";
import { LUNCHBOXES } from "../../../../model/lunchboxes.model";

@Component({
    templateUrl: './lunchbox-paint.component.html',
    styleUrls: ['./lunchbox-paint.component.scss'],
    standalone: false
})
export class LunchboxPaintComponent implements OnInit, OnDestroy
{
  LUNCHBOXES = LUNCHBOXES;

  state = 'findPet';
  pet: MyPetSerializationGroup;
  loading = false;
  serumId: number;
  speciesAjax: Subscription;
  newPattern: number|null = null;

  constructor(
    private api: ApiService, private router: Router,
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

  doCancel()
  {
    if(this.loading) return;

    this.pet = null;
    this.state = 'findPet';
  }

  doSelectPet(pet: MyPetSerializationGroup)
  {
    if(pet === null) return;

    this.pet = pet;
    this.state = 'injectPet';
    this.newPattern = this.pet.lunchboxIndex;
  }

  doPaint()
  {
    if(this.loading) return;

    this.loading = true;

    const data = {
      pet: this.pet.id,
      lunchboxIndex: this.newPattern,
    };

    this.api.patch('/item/lunchboxPaint/' + this.serumId + '/paint', data).subscribe({
      next: () => {
        this.router.navigate([ '/home' ]);
      },
      error: () => {
        this.loading = false;
      }
    });
  }

}
