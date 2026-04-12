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
import {MyInventorySerializationGroup} from "../../../../model/my-inventory/my-inventory.serialization-group";
import {Subscription} from "rxjs";
import {ApiService} from "../../../shared/service/api.service";
import {ActivatedRoute, Router} from "@angular/router";
import {ApiResponseModel} from "../../../../model/api-response.model";

@Component({
    selector: 'app-hot-pot',
    templateUrl: './hot-pot.component.html',
    styleUrls: ['./hot-pot.component.scss'],
    standalone: false
})
export class HotPotComponent implements OnInit {

  hotPotId: number;
  dipping = false;
  inventory: MyInventorySerializationGroup[];
  inventoryAjax: Subscription;

  constructor(private api: ApiService, private activatedRoute: ActivatedRoute, private router: Router) { }

  ngOnInit() {
    this.hotPotId = parseInt(this.activatedRoute.snapshot.paramMap.get('id'));

    this.inventoryAjax = this.api.get<MyInventorySerializationGroup[]>('/inventory/my').subscribe({
      next: (r: ApiResponseModel<MyInventorySerializationGroup[]>) => {
        this.inventory = r.data.filter(i => !!i.item.food);
      }
    });
  }

  ngOnDestroy(): void {
    this.inventoryAjax.unsubscribe();
  }

  doDipItem(food: MyInventorySerializationGroup)
  {
    if(this.dipping) return;

    this.dipping = true;

    this.api.post('/item/hotPot/' + this.hotPotId + '/dip', { food: food.id }).subscribe({
      next: () => {
        this.router.navigate([ '/home' ]);
      },
      error: () => {
        this.dipping = false;
      }
    })
  }

}
