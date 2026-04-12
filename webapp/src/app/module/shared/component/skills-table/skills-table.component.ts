/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
