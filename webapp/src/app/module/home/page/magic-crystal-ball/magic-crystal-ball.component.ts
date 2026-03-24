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