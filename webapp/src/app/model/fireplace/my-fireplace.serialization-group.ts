import { HelperPetSerializationGroup } from "../helper-pet.serialization-group";

export interface MyFireplaceSerializationGroup
{
  longestStreak: number;
  currentStreak: number;
  heat: number;
  heatDescription: number;
  hasReward: boolean;
  hasForge: boolean;
  bricks: number;
  stocking: { appearance: string, colorA: string, colorB: string };
  mantleSize: number;
  helper: HelperPetSerializationGroup|null;
}
