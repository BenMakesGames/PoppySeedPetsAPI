/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, input, output } from '@angular/core';
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import { HelpLinkComponent } from "../help-link/help-link.component";
import { SkillsTableComponent } from "../skills-table/skills-table.component";
import { SkillBonusesComponent } from "../skill-bonuses/skill-bonuses.component";
import { AttributesTableComponent } from "../attributes-table/attributes-table.component";

@Component({
  imports: [
    HelpLinkComponent,
    SkillsTableComponent,
    SkillBonusesComponent,
    AttributesTableComponent,
  ],
  selector: 'app-pet-skills-and-attributes-panel',
  templateUrl: './pet-skills-and-attributes-panel.component.html',
  styleUrls: ['./pet-skills-and-attributes-panel.component.scss'],
})
export class PetSkillsAndAttributesPanelComponent {
  pet = input.required<MyPetSerializationGroup>();

  clickedHelpLink = output();
}
