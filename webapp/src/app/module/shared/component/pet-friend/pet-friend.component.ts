import {Component, Input} from '@angular/core';
import {PetFriendSerializationGroup} from "../../../../model/my-pet/pet-friend.serialization-group";
import { PetAppearanceComponent } from "../pet-appearance/pet-appearance.component";
import { CommonModule } from "@angular/common";
import { HeartMeterComponent } from "../heart-meter/heart-meter.component";

@Component({
    selector: 'app-pet-friend',
    templateUrl: './pet-friend.component.html',
    imports: [
        PetAppearanceComponent,
        CommonModule,
        HeartMeterComponent
    ],
    styleUrls: ['./pet-friend.component.scss']
})
export class PetFriendComponent {

  DESCRIBE_CURRENT_RELATIONSHIP = {
    'friend': 'are friends',
    'friendly rival': 'are friendly rivals',
    'broke up': 'broke up',
    'dislike': 'don\'t like each other',
    'fwb': 'are FWBs',
    'bff': 'are BFFs',
    'mate': 'are dating',
  };

  DESCRIBE_GOAL_RELATIONSHIP = {
    'friend': 'be friends',
    'friendly rival': 'be friendly rivals',
    'dislike': 'end the relationship',
    'fwb': 'be FWBs',
    'bff': 'be BFFs',
    'mate': 'date',
  };

  @Input() pet: { name: string };
  @Input() friend: PetFriendSerializationGroup;
  @Input() showRating = false;
}
