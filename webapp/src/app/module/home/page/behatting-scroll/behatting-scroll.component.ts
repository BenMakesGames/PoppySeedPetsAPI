import { Component, OnInit } from '@angular/core';
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import {ApiService} from "../../../shared/service/api.service";
import {ActivatedRoute, Router} from "@angular/router";
import { ItemOtherPropertiesIcons } from "../../../../model/item-other-properties-icons";

@Component({
    templateUrl: './behatting-scroll.component.html',
    styleUrls: ['./behatting-scroll.component.scss'],
    standalone: false
})
export class BehattingScrollComponent implements OnInit {

  scrollId: number;
  behatting = false;

  hatDescriptions = [
    'incredibly-cosmetic',
    'beautifully-cosmetic',
    'cosmically-cosmetic',
    'fashionably-cosmetic',
    '',
  ];

  incredibleDescriptions = [
    'POWERFUL',
    'AWE-INSPIRING',
    'BEWILDERING',
    'MIND-BLOWING',
    'WONDROUS',
    'EXCEPTIONAL',
    'WILDLY-OP',
  ];

  hatDescription: string;
  incredibleDescription: string;

  readonly itemOtherPropertiesIcons = ItemOtherPropertiesIcons;

  constructor(private api: ApiService, private router: Router, private activatedRoute: ActivatedRoute)
  {

  }

  ngOnInit()
  {
    this.hatDescription = this.hatDescriptions[Math.floor(Math.random() * this.hatDescriptions.length)];
    this.incredibleDescription = this.incredibleDescriptions[Math.floor(Math.random() * this.incredibleDescriptions.length)];

    this.scrollId = parseInt(this.activatedRoute.snapshot.paramMap.get('id'));
  }

  doBehat(pet: MyPetSerializationGroup)
  {
    if(!pet) return;

    if(this.behatting) return;

    this.behatting = true;

    this.api.patch('/item/behattingScroll/' + this.scrollId + '/read', { pet: pet.id })
      .subscribe({
        next: () => {
          this.router.navigate([ '/home' ]);
        },
        error: () => {
          this.behatting = false;
        }
      })
  }

}
