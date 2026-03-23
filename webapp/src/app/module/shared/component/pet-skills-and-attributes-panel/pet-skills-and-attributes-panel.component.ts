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
