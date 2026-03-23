import {Component, Input} from '@angular/core';
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import { PetAppearanceComponent } from "../pet-appearance/pet-appearance.component";
import { PetStatusEffectsComponent } from "../pet-status-effects/pet-status-effects.component";

@Component({
    selector: 'app-pet-mini',
    templateUrl: './pet-mini.component.html',
    imports: [
        PetAppearanceComponent,
        PetStatusEffectsComponent
    ],
    styleUrls: ['./pet-mini.component.scss']
})
export class PetMiniComponent {

  @Input() pet: MyPetSerializationGroup;
  @Input() size = '1in';

  constructor() { }
}
