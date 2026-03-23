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