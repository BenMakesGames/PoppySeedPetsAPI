import {Component, Input} from '@angular/core';
import {ComputedPetSkillsSerializationGroup} from "../../../../model/my-pet/computed-pet-skills.serialization-group";
import { StarsComponent } from "../stars/stars.component";
import { PetSkillModifiersComponent } from "../pet-skill-modifiers/pet-skill-modifiers.component";

@Component({
    selector: 'app-attributes-table',
    templateUrl: './attributes-table.component.html',
    imports: [
        StarsComponent,
        PetSkillModifiersComponent
    ],
    styleUrls: ['./attributes-table.component.scss']
})
export class AttributesTableComponent {

  @Input() attributes: ComputedPetSkillsSerializationGroup;

}
