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
