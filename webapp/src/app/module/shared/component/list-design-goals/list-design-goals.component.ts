import { Component, Input, OnInit } from '@angular/core';
import { CommonModule } from "@angular/common";
import { RouterLink } from "@angular/router";

@Component({
    imports: [
        CommonModule,
        RouterLink
    ],
    selector: 'app-list-design-goals',
    templateUrl: './list-design-goals.component.html',
    styleUrls: ['./list-design-goals.component.scss']
})
export class ListDesignGoalsComponent implements OnInit {

  @Input() goals: { id: number, name: string }[] = [];

  constructor() { }

  ngOnInit(): void {
  }

}
