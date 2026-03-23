import { Component, Input, OnChanges, SimpleChanges } from '@angular/core';
import {PetSkillsEnum} from "../../../../model/pet-skills.enum";
import {ComputedPetSkillsSerializationGroup} from "../../../../model/my-pet/computed-pet-skills.serialization-group";
import { SkillTableRowComponent } from "../skill-table-row/skill-table-row.component";
import { CommonModule } from "@angular/common";

@Component({
    selector: 'app-skills-table',
    templateUrl: './skills-table.component.html',
    imports: [
        SkillTableRowComponent,
        CommonModule
    ],
    styleUrls: ['./skills-table.component.scss']
})
export class SkillsTableComponent implements OnChanges {

  hasAnySkills = false;

  skillList = Object.keys(PetSkillsEnum).map(k => PetSkillsEnum[k]).sort();
  petLevel: number;

  @Input() skills: ComputedPetSkillsSerializationGroup;

  ngOnChanges(changes: SimpleChanges): void {
    this.hasAnySkills = this.skillList.some(s =>
      this.skills[s].base > 0 ||
      this.skills[s].merits > 0 ||
      this.skills[s].tool > 0 ||
      this.skills[s].statusEffects > 0
    );
    this.petLevel = this.skillList.reduce((total, skill) => total + this.skills[skill].base, 0);
  }
}
