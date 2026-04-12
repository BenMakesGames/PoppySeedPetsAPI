/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { LoadingThrobberComponent } from "../loading-throbber/loading-throbber.component";
import { CommonModule } from "@angular/common";
import { FormsModule } from "@angular/forms";
import { ApiService } from "../../service/api.service";
import { Subscription } from 'rxjs';
import { FilterResultsSerializationGroup } from "../../../../model/filter-results.serialization-group";

@Component({
    selector: 'app-select-merit',
    templateUrl: './select-merit.component.html',
    imports: [
        CommonModule,
        FormsModule,
        LoadingThrobberComponent,
    ],
    styleUrls: ['./select-merit.component.scss']
})
export class SelectMeritComponent implements OnInit {

  static meritSubscription = Subscription.EMPTY;
  static cachedMerits: MeritModel[]|null = null;

  merits: MeritModel[]|null = null;

  constructor(private api: ApiService) {
  }

  ngOnInit(): void {
    this.loadMerits();
  }

  private loadMerits()
  {
    if(SelectMeritComponent.cachedMerits !== null)
    {
      this.merits = SelectMeritComponent.cachedMerits;
      return;
    }

    if(!SelectMeritComponent.meritSubscription.closed)
      return;

    SelectMeritComponent.meritSubscription = this.api.get<FilterResultsSerializationGroup<MeritModel>>('/encyclopedia/merit', { pageSize: 200 }).subscribe({
      next: response => {
        SelectMeritComponent.cachedMerits = response.data.results.sort((a, b) => a.name.localeCompare(b.name));

        this.merits = SelectMeritComponent.cachedMerits;
      }
    });
  }

  @Input() anyLabel: string|null = 'Any';

  @Input() value: number|null = null;
  @Output() valueChange = new EventEmitter<number|null>();

}

interface MeritModel
{
  id: number;
  name: string;
  description: string;
}