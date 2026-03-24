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
