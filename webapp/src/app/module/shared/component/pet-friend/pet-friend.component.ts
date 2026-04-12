/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
