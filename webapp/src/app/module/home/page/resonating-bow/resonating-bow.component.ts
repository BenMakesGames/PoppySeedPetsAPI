import {Component, OnDestroy, OnInit} from '@angular/core';
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import {ApiService} from "../../../shared/service/api.service";
import {ActivatedRoute, Router} from "@angular/router";
import {Subscription} from "rxjs";

@Component({
    templateUrl: './resonating-bow.component.html',
    styleUrls: ['./resonating-bow.component.scss'],
    standalone: false
})
export class ResonatingBowComponent implements OnInit, OnDestroy
{
  state = 'findPet';
  pet: MyPetSerializationGroup;
  aOrB: string|null = null;
  loading = false;
  serumId: number;
  speciesAjax: Subscription;
  availableColors: { css: string, shift: number }[] = [];
  selectedShift: number|null = null;

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

  doSelectPet(pet: MyPetSerializationGroup)
  {
    if(pet === null) return;

    this.pet = pet;
    this.aOrB = null;
    this.selectedShift = null;
  }

  doChooseColor(color: string)
  {
    this.aOrB = color;

    this.availableColors = [];
    this.selectedShift = null;

    const baseRgb = color === 'A' ? this.pet.colorA : this.pet.colorB;

    for(let i = -3; i <= 3; i++)
    {
      if(i === 0)
      {
        this.availableColors.push({ css: `#${baseRgb}`, shift: 0 });
      }
      else
      {
        const hueShiftPercent = i / 30;

        // CSS, convert rgb to hsl, and shift hue by percent
        this.availableColors.push({
          css: `hsl(from #${baseRgb} calc(h + ${hueShiftPercent * 360}) s l)`,
          shift: i
        });
      }
    }
  }

  doPaint()
  {
    if(this.loading || this.selectedShift === null) return;

    this.loading = true;

    const data = {
      pet: this.pet.id,
      color: this.aOrB,
      hueShift: this.selectedShift,
    };

    this.api.patch('/item/resonatingBow/' + this.serumId + '/tweakHue', data).subscribe({
      next: () => {
        this.router.navigate([ '/home' ]);
      },
      error: () => {
        this.loading = false;
      }
    });
  }
}
