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
import { ActivatedRoute, Router } from "@angular/router";

@Component({
    templateUrl: './dragon-tongue.component.html',
    styleUrls: ['./dragon-tongue.component.scss'],
    standalone: false
})
export class DragonTongueComponent implements OnInit {
  inventoryId: number;

  speech: {greetings: string[], thanks: string[]}|null = null;
  loading = true;
  saving = false;

  constructor(
    private api: ApiService, private activatedRoute: ActivatedRoute,
    private router: Router
  )
  {
  }

  ngOnInit()
  {
    this.inventoryId = parseInt(this.activatedRoute.snapshot.paramMap.get('id'));

    this.api.get<{greetings: string[], thanks: string[]}>('/item/dragonTongue/' + this.inventoryId + '/speech')
      .subscribe({
        next: r => {
          this.speech = r.data;
          this.loading = false;
        },
        error: () => {
          this.loading = false;
        }
      });
  }

  doSetSpeech()
  {
    if(this.saving) return;

    this.saving = true;

    this.api.post<any>('/item/dragonTongue/' + this.inventoryId + '/speech', this.speech)
      .subscribe({
        next: (r) => {
          this.router.navigateByUrl('/home');
        },
        error: () => {
          this.saving = false;
        }
      })
    ;
  }

}
