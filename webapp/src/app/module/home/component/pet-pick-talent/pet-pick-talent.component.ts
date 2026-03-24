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
