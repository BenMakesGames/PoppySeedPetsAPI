import { HelperPetSerializationGroup } from "./helper-pet.serialization-group";

export interface MyBeehiveSerializationGroup
{
  workers: number;
  queenName: string;
  flowerPowerPercent: number;
  isWorking: boolean;
  flowerPowerIsMaxed: boolean;
  royalJellyPercent: number;
  honeycombPercent: number;
  miscPercent: number;
  helper: HelperPetSerializationGroup|null;
}
