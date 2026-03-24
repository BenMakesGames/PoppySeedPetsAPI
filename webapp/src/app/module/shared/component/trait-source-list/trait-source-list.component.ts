import {Component, Input, OnInit} from '@angular/core';
import {TotalPetSkillsSerializationGroup} from "../../../../model/my-pet/total-pet-skills.serialization-group";
import { CommonModule } from "@angular/common";

@Component({
    imports: [
        CommonModule
    ],
    selector: 'app-trait-source-list',
    templateUrl: './trait-source-list.component.html',
    styleUrls: ['./trait-source-list.component.scss']
})
export class TraitSourceListComponent implements OnInit {

  @Input() skills: TotalPetSkillsSerializationGroup;

  constructor() { }

  ngOnInit(): void {
  }

}
