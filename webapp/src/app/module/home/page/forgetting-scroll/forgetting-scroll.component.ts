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
