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
import {ActivatedRoute} from "@angular/router";

@Component({
    templateUrl: './rijndael.component.html',
    styleUrls: ['./rijndael.component.scss'],
    standalone: false
})
export class RijndaelComponent implements OnInit {
  inventoryId: number;

  doingIt = false;

  results: any[];

  constructor(private api: ApiService, private activatedRoute: ActivatedRoute)
  {

  }

  ngOnInit()
  {
    this.inventoryId = parseInt(this.activatedRoute.snapshot.paramMap.get('id'));
  }

  doIt(itemId: number|null)
  {
    if(itemId === null) return;

    if(this.doingIt) return;

    this.doingIt = true;

    this.api.post<any>('/item/rijndael/' + this.inventoryId, { itemId: itemId })
      .subscribe({
        next: (r) => {
          this.results = r.data;
        },
        error: () => {
          this.doingIt = false;
        }
      })
    ;
  }

}
