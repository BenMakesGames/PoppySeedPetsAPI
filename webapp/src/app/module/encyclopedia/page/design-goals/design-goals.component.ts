import { Component, OnInit } from '@angular/core';
import { ApiService } from "../../../shared/service/api.service";

@Component({
    templateUrl: './design-goals.component.html',
    styleUrls: ['./design-goals.component.scss'],
    standalone: false
})
export class DesignGoalsComponent implements OnInit {

  designGoals: { id: number, name: string }[] = [];

  constructor(private api: ApiService) { }

  ngOnInit(): void {

    this.api.get<{ name: string, id: number }[]>('/designGoal').subscribe({
      next: r => {
        this.designGoals = r.data.sort((a, b) => a.name.localeCompare(b.name));
      }
    });
  }

}
