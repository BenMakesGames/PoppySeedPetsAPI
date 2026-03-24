import { Component, Input } from '@angular/core';
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import { PetAppearanceComponent } from "../pet-appearance/pet-appearance.component";
import { PetStatusEffectsComponent } from "../pet-status-effects/pet-status-effects.component";
import { CommonModule } from "@angular/common";
import { NeedBarComponent } from "../need-bar/need-bar.component";
import { MinuteTimerComponent } from "../minute-timer/minute-timer.component";

@Component({
    selector: 'app-pet',
    templateUrl: './pet.component.html',
    imports: [
        PetAppearanceComponent,
        PetStatusEffectsComponent,
        CommonModule,
        NeedBarComponent,
        NeedBarComponent,
        MinuteTimerComponent,
    ],
    styleUrls: ['./pet.component.scss']
})
export class PetComponent {
  @Input() pet: MyPetSerializationGroup;
}
