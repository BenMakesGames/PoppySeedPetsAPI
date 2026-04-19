/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, EventEmitter, Input, Output} from '@angular/core';
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";

@Component({
  selector: 'app-pet-pick-talent',
  templateUrl: './pet-pick-talent.component.html',
  styleUrls: ['./pet-pick-talent.component.scss']
})
export class PetPickTalentComponent {

  @Input() pet: MyPetSerializationGroup;
  @Output() selectTalent = new EventEmitter<PetPickTalentSelectModel>();

  constructor() { }

  doPickTalent(type: string, merit: string)
  {
    this.selectTalent.emit({ type: type, merit: merit });
  }
}

export interface PetPickTalentSelectModel
{
  type: string;
  merit: string;
}
