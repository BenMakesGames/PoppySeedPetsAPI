import { HelperPetSerializationGroup } from "./helper-pet.serialization-group";
import { DragonHostageSerializationGroup } from "./dragon-hostage.serialization-group";

export interface MyDragonSerializationGroup
{
  name: string;
  colorA: string;
  colorB: string;
  treasureCount: number;
  silver: number;
  gold: number;
  gems: number;
  greetings: string[];
  thanks: string[];
  appearance: number;
  helper: HelperPetSerializationGroup|null;
  hostage: DragonHostageSerializationGroup|null;
}
