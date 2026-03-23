import {Component, Input, OnChanges} from '@angular/core';
import {TotalPetSkillsSerializationGroup} from "../../../../model/my-pet/total-pet-skills.serialization-group";
import { CommonModule } from "@angular/common";

@Component({
    imports: [
        CommonModule
    ],
    selector: 'app-stars',
    templateUrl: './stars.component.html',
    styleUrls: ['./stars.component.scss']
})
export class StarsComponent implements OnChanges {

  stars: number[];

  @Input() value: TotalPetSkillsSerializationGroup;

  constructor() { }

  ngOnChanges()
  {
    this.stars = [];

    for(let i = 0; i < this.value.base; i++)
      this.stars.push(1);
  }

}
