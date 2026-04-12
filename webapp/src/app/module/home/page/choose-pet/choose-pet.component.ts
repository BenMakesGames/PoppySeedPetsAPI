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
import {ApiService} from "../../../shared/service/api.service";
import {ActivatedRoute, Router} from "@angular/router";
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import { ItemActionResponseDialog } from "../../../../dialog/item-action-response/item-action-response.dialog";
import { MatDialog } from "@angular/material/dialog";

@Component({
    templateUrl: './choose-pet.component.html',
    styleUrls: ['./choose-pet.component.scss'],
    standalone: false
})
export class ChoosePetComponent implements OnInit {
  inventoryId: number;
  route: string;

  butWho: string = 'Who Will Do It?';
  chooseAPet: string = 'Choose a Pet';
  doingIt = false;

  constructor(
    private api: ApiService, private router: Router, private activatedRoute: ActivatedRoute,
    private matDialog: MatDialog
  )
  {

  }

  ngOnInit()
  {
    this.inventoryId = parseInt(this.activatedRoute.snapshot.paramMap.get('id'));
    this.route = this.activatedRoute.snapshot.paramMap.get('route');

    switch(this.route)
    {
      case 'brawlSkillScroll':
        this.butWho = 'Who Will Learn Brawl?';
        break;

      case 'craftsSkillScroll':
        this.butWho = 'Who Will Learn Crafts?';
        break;

      case 'musicSkillScroll':
        this.butWho = 'Who Will Learn Music?';
        break;

      case 'natureSkillScroll':
        this.butWho = 'Who Will Learn Nature?';
        break;

      case 'scienceSkillScroll':
        this.butWho = 'Who Will Learn Science?';
        break;

      case 'stealthSkillScroll':
        this.butWho = 'Who Will Learn Stealth?';
        break;

      case 'arcanaSkillScroll':
        this.butWho = 'Who Will Learn Arcana?';
        break;

      case 'magicMirror':
        this.butWho = 'Magic Mirror';
        break;

      case 'pandemirrorum':
        this.butWho = 'Pandemirrorum';
        break;

      case 'magicBrush':
        this.butWho = 'Magic Brush';
        break;

      case 'birdBathBlueprint':
        this.butWho = 'Build a Bird Bath';
        this.chooseAPet = 'Choose a Pet to Help';
        break;

      case 'forgeBlueprint':
        this.butWho = 'Build a Forge';
        this.chooseAPet = 'Choose a Pet to Help';
        break;

      case 'fishStatue':
        this.butWho = 'Install a Fish Statue';
        this.chooseAPet = 'Choose a Pet to Help';
        break;

      case 'moondialBlueprint':
        this.butWho = 'Build a Moondial';
        this.chooseAPet = 'Choose a Pet to Help';
        break;

      case 'beehiveBlueprint':
        this.butWho = 'Build a Beehive';
        this.chooseAPet = 'Choose a Pet to Help';
        break;

      case 'basementBlueprint':
        this.butWho = '"Build" a Basement?';
        this.chooseAPet = 'Choose a Pet to Help';
        break;

      case 'greenhouseDeed':
        this.butWho = 'Clear a Greenhouse Space';
        this.chooseAPet = 'Choose a Pet to Help';
        break;

      case 'installComposter':
        this.butWho = 'Install Composter';
        this.chooseAPet = 'Choose a Pet to Help';
        break;

      case 'tremendousTea':
      case 'tinyTea':
      case 'totallyTea':
        this.butWho = 'Who Will Drink This Size-changing Elixir?';
        break;

      case 'werebane':
        this.butWho = 'Who Will Drink the Werebane?';
        break;

      case 'pocketDimension':
        this.butWho = 'Expand a Lunchbox';
        break;

      case 'changeWereform':
        this.butWho = 'Whose Wereform Will Be Changed?';
        this.chooseAPet = 'Choose a Pet in Wereform';
        break;

      case 'blushOfLife':
        this.butWho = 'Who Will Drink It?';
        this.chooseAPet = 'Choose a Pet to Gain "Blush of Life"';
        break;

      case 'hyperchromaticPrism':
        this.butWho = 'Shine On...';
        this.chooseAPet = 'Choose a Pet to Gain "Hyperchromatic"';
        break;

      case 'extractHyperchromaticPrism':
        this.butWho = 'Cut';
        this.chooseAPet = 'Choose a Pet to Lose "Hyperchromatic"';
        break;
    }
  }

  doIt(pet: MyPetSerializationGroup)
  {
    if(pet === null) return;

    if(this.doingIt) return;

    this.doingIt = true;

    this.api.post<any>('/item/' + this.route + '/' + this.inventoryId, { pet: pet.id })
      .subscribe({
        next: r => {
          if(r.data?.text)
            ItemActionResponseDialog.open(this.matDialog, r.data, null);

          this.router.navigate([ '/home' ]);
        },
        error: () => {
          this.doingIt = false;
        }
      })
    ;
  }
}
