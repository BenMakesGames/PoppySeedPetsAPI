import {Component, Input, OnChanges} from '@angular/core';
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import {STATUS_EFFECTS} from "../../../../model/status-effects";
import { CommonModule } from "@angular/common";

@Component({
    imports: [
        CommonModule
    ],
    selector: 'app-pet-status-effects',
    templateUrl: './pet-status-effects.component.html',
    styleUrls: ['./pet-status-effects.component.scss']
})
export class PetStatusEffectsComponent implements OnChanges {

  @Input({ required: true }) pet: MyPetSerializationGroup;
  @Input() centered = false;
  @Input() max = 8;

  statusEffectImages: string[];
  showEllipsis = false;

  constructor() { }

  ngOnChanges()
  {
    this.statusEffectImages = this.pet.statuses.map(s => {
      return STATUS_EFFECTS[s].icon;
    });

    if(this.pet.poisonLevel !== 'none') this.statusEffectImages.push('poison');
    if(this.pet.alcoholLevel !== 'none') this.statusEffectImages.push('alcohol');
    if(this.pet.hallucinogenLevel !== 'none') this.statusEffectImages.push('hallucinogen');
    if(this.pet.craving) this.statusEffectImages.push('craving');
    if(this.pet.pregnancy) this.statusEffectImages.push('pregnant');

    if(this.statusEffectImages.length > this.max)
    {
      this.statusEffectImages = this.statusEffectImages.sort().splice(0, this.max - 1);
      this.showEllipsis = true;
    }
    else
    {
      this.statusEffectImages = this.statusEffectImages.sort();
      this.showEllipsis = false;
    }
  }
}
