import {Component, Input} from '@angular/core';
import {TotalPetSkillsSerializationGroup} from "../../../../model/my-pet/total-pet-skills.serialization-group";
import { CommonModule } from "@angular/common";

@Component({
    imports: [
        CommonModule
    ],
    selector: 'app-pet-skill-modifiers',
    templateUrl: './pet-skill-modifiers.component.html',
    styleUrls: ['./pet-skill-modifiers.component.scss']
})
export class PetSkillModifiersComponent {

  @Input() skill: TotalPetSkillsSerializationGroup;

}
