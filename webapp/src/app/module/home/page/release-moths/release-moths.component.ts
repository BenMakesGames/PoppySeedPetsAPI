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

@Component({
    templateUrl: './release-moths.component.html',
    styleUrls: ['./release-moths.component.scss'],
    standalone: false
})
export class ReleaseMothsComponent implements OnInit {
  inventoryId: number;
  location: number;
  locationDescription: string = '';
  numberToSend: number = 1;
  now: Date = new Date();
  route: string;

  mothsAvailable: number|null = null;

  sendingMoths = false;

  constructor(private api: ApiService, private router: Router, private activatedRoute: ActivatedRoute)
  {

  }

  ngOnInit()
  {
    this.inventoryId = parseInt(this.activatedRoute.snapshot.paramMap.get('id'));

    this.api.get<{ location: number, quantity: number }>('/item/moth/getQuantity/' + this.inventoryId).subscribe({
      next: r => {
        this.location = r.data.location;
        this.mothsAvailable = r.data.quantity;

        if(this.location == 0)
          this.locationDescription = 'at home';
        else if(this.location == 1)
          this.locationDescription = 'in your basement';
        else if(this.location == 2)
          this.locationDescription = 'on your mantle';
      }
    })
  }

  doSendMoths()
  {
    if(this.sendingMoths) return;

    this.sendingMoths = true;

    this.api.post<any>('/item/moth/release', { location: this.location, count: this.numberToSend })
      .subscribe({
        next: (r) => {
          if(this.location == 1)
            this.router.navigate([ '/basement' ]);
          else if(this.location == 2)
            this.router.navigate([ '/fireplace' ]);
          else
            this.router.navigate([ '/home' ]);
        },
        error: () => {
          this.sendingMoths = false;
        }
      })
    ;
  }

}
