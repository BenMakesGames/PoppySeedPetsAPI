import {TotalPetSkillsSerializationGroup} from "./total-pet-skills.serialization-group";

export interface ComputedPetSkillsSerializationGroup {
  dexterity: TotalPetSkillsSerializationGroup;
  strength: TotalPetSkillsSerializationGroup;
  stamina: TotalPetSkillsSerializationGroup;
  intelligence: TotalPetSkillsSerializationGroup;
  perception: TotalPetSkillsSerializationGroup;

  arcana: TotalPetSkillsSerializationGroup;
  brawl: TotalPetSkillsSerializationGroup;
  crafts: TotalPetSkillsSerializationGroup;
  music: TotalPetSkillsSerializationGroup;
  nature: TotalPetSkillsSerializationGroup;
  science: TotalPetSkillsSerializationGroup;
  stealth: TotalPetSkillsSerializationGroup;

  climbingBonus: TotalPetSkillsSerializationGroup;
  electronicsBonus: TotalPetSkillsSerializationGroup;
  fishingBonus: TotalPetSkillsSerializationGroup;
  gatheringBonus: TotalPetSkillsSerializationGroup;
  hackingBonus: TotalPetSkillsSerializationGroup;
  magicBindingBonus: TotalPetSkillsSerializationGroup;
  miningBonus: TotalPetSkillsSerializationGroup;
  physicsBonus: TotalPetSkillsSerializationGroup;
  smithingBonus: TotalPetSkillsSerializationGroup;
  umbraBonus: TotalPetSkillsSerializationGroup;

  canSeeInTheDark: TotalPetSkillsSerializationGroup;
  hasProtectionFromHeat: TotalPetSkillsSerializationGroup;
  hasProtectionFromElectricity: TotalPetSkillsSerializationGroup;
}
