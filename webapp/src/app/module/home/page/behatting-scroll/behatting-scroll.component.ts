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
