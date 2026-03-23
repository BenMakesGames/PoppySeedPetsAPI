import {Component, Input, OnInit} from '@angular/core';
import {TotalPetSkillsSerializationGroup} from "../../../../model/my-pet/total-pet-skills.serialization-group";
import { StatBarComponent } from "../stat-bar/stat-bar.component";
import { PetSkillModifiersComponent } from "../pet-skill-modifiers/pet-skill-modifiers.component";
import { CommonModule, TitleCasePipe } from "@angular/common";

@Component({
    selector: '[appSkillTableRow]',
    templateUrl: './skill-table-row.component.html',
    imports: [
        StatBarComponent,
        PetSkillModifiersComponent,
        TitleCasePipe,
        CommonModule
    ],
    styleUrls: ['./skill-table-row.component.scss']
})
export class SkillTableRowComponent implements OnInit {

  @Input() compact: boolean = false;
  @Input() skill: TotalPetSkillsSerializationGroup;
  @Input() name: string;
  @Input() index: number;

  constructor() { }

  ngOnInit(): void {
  }

}
