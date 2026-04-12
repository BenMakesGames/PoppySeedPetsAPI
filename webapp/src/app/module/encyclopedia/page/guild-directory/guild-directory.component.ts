/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, OnDestroy, OnInit} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {GuildEncyclopediaSerializationGroup} from "../../../../model/encyclopedia/guild-encyclopedia.serialization-group";
import {Subscription} from "rxjs";

@Component({
    selector: 'app-guild-directory',
    templateUrl: './guild-directory.component.html',
    styleUrls: ['./guild-directory.component.scss'],
    standalone: false
})
export class GuildDirectoryComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Poppyopedia - Guilds' };

  guilds: GuildEncyclopediaSerializationGroup[];

  guildsAjax: Subscription;

  constructor(private api: ApiService) { }

  ngOnInit(): void {
    this.guildsAjax = this.api.get<GuildEncyclopediaSerializationGroup[]>('/guild').subscribe({
      next: r => {
        this.guilds = r.data;
      }
    })
  }

  ngOnDestroy(): void {
    this.guildsAjax.unsubscribe();
  }

}
