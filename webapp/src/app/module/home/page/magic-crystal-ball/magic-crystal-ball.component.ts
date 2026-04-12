/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {ActivatedRoute} from "@angular/router";
import { MyPetSerializationGroup } from "../../../../model/my-pet/my-pet.serialization-group";

@Component({
    templateUrl: './magic-crystal-ball.component.html',
    styleUrls: ['./magic-crystal-ball.component.scss'],
    standalone: false
})
export class MagicCrystalBallComponent {

  disableButtons = false;
  magicCrystalBallId: number;
  state = 'chooseOne';

  offspringData: PredictOffspringResponse|null = null;
  fateData: CreateFateResponse|null = null;
  fatedPetName: string|null = null;
  rarePetDayData: SeeNextRarePetDayResponse|null = null;

  constructor(
    private api: ApiService, private activatedRoute: ActivatedRoute
  )
  {
  }

  ngOnInit()
  {
    this.magicCrystalBallId = parseInt(this.activatedRoute.snapshot.paramMap.get('id'));
  }

  doPredictOffspring(pet: MyPetSerializationGroup)
  {
    if(!pet)
      return;

    if(this.disableButtons)
      return;

    this.disableButtons = true;

    this.api.post<PredictOffspringResponse>('/item/magicCrystalBall/' + this.magicCrystalBallId + '/predictOffspring', { petId: pet.id }).subscribe({
      next: r => {
        this.state = 'showOffspring';
        this.offspringData = r.data;
        this.disableButtons = false;
      },
      error: _ => {
        this.disableButtons = false;
      }
    });
  }

  doCreateFate(pet: MyPetSerializationGroup)
  {
    if(!pet)
      return;

    if(this.disableButtons)
      return;

    this.disableButtons = true;
    this.fatedPetName = pet.name;

    this.api.post<CreateFateResponse>('/item/magicCrystalBall/' + this.magicCrystalBallId + '/createFate', { petId: pet.id }).subscribe({
      next: r => {
        this.state = 'showFate';
        this.fateData = r.data;
        this.disableButtons = false;
      },
      error: _ => {
        this.disableButtons = false;
      }
    });
  }

  doSeeNextRarePetDay()
  {
    if(this.disableButtons)
      return;

    this.disableButtons = true;

    this.api.post<SeeNextRarePetDayResponse>('/item/magicCrystalBall/' + this.magicCrystalBallId + '/findNextRarePetDay').subscribe({
      next: r => {
        this.state = 'nextPetDay';
        this.disableButtons = false;
        this.rarePetDayData = r.data;
      },
      error: _ => {
        this.disableButtons = false;
      }
    });
  }

}

interface PredictOffspringResponse
{
  colorA: string;
  colorB: string;
  speciesImage: string;
}

interface SeeNextRarePetDayResponse
{
  date: string;
}

interface CreateFateResponse
{
  description: string;
}