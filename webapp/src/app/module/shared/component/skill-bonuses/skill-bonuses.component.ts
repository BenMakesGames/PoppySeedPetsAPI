/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, Input, OnChanges} from '@angular/core';
import {TotalPetSkillsSerializationGroup} from "../../../../model/my-pet/total-pet-skills.serialization-group";
import {ComputedPetSkillsSerializationGroup} from "../../../../model/my-pet/computed-pet-skills.serialization-group";
import { PetSkillModifiersComponent } from "../pet-skill-modifiers/pet-skill-modifiers.component";
import { CommonModule } from "@angular/common";
import { TraitSourceListComponent } from "../trait-source-list/trait-source-list.component";

@Component({
    selector: 'app-skill-bonuses',
    templateUrl: './skill-bonuses.component.html',
    imports: [
        PetSkillModifiersComponent,
        CommonModule,
        TraitSourceListComponent
    ],
    styleUrls: ['./skill-bonuses.component.scss']
})
export class SkillBonusesComponent implements OnChanges {

  @Input() skills: ComputedPetSkillsSerializationGroup;

  showClimbingBonus = false;
  showElectronicsBonus = false;
  showFishingBonus = false;
  showGatheringBonus = false;
  showHackingBonus = false;
  showPhysicsBonus = false;
  showSmithingBonus = false;
  showMagicBindingBonus = false;
  showMiningBonus = false;
  showUmbraBonus = false;

  constructor() { }

  ngOnChanges(): void {
    this.showClimbingBonus = this.showBonus(this.skills.climbingBonus);
    this.showElectronicsBonus = this.showBonus(this.skills.electronicsBonus);
    this.showFishingBonus = this.showBonus(this.skills.fishingBonus);
    this.showGatheringBonus = this.showBonus(this.skills.gatheringBonus);
    this.showHackingBonus = this.showBonus(this.skills.hackingBonus);
    this.showPhysicsBonus = this.showBonus(this.skills.physicsBonus);
    this.showSmithingBonus = this.showBonus(this.skills.smithingBonus);
    this.showMagicBindingBonus = this.showBonus(this.skills.magicBindingBonus);
    this.showMiningBonus = this.showBonus(this.skills.miningBonus);
    this.showUmbraBonus = this.showBonus(this.skills.umbraBonus);
  }

  showBonus(bonus: TotalPetSkillsSerializationGroup)
  {
    return bonus.merits !== 0 || bonus.tool !== 0 || bonus.statusEffects !== 0;
  }

}
