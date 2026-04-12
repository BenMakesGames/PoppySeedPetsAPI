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
import { ApiService } from "../../../shared/service/api.service";
import { FilterResultsSerializationGroup } from "../../../../model/filter-results.serialization-group";

@Component({
    selector: 'app-list',
    templateUrl: './list.component.html',
    styleUrls: ['./list.component.scss'],
    standalone: false
})
export class ListComponent implements OnInit {

  months = [ '', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' ];

  stories: FilterResultsSerializationGroup<StoryDto>|null = null;

  constructor(private api: ApiService) { }

  ngOnInit(): void {
    this.api.get<FilterResultsSerializationGroup<StoryDto>>('/starKindred').subscribe({
      next: r => {
        this.stories = r.data;
      }
    });
  }

}

interface StoryDto
{
  id: number;
  title: string;
  summary: string;
  releaseNumber: number;
  releaseYear: number;
  releaseMonth: number;
}