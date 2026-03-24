import { HelperPetSerializationGroup } from "../helper-pet.serialization-group";

export interface MyGreenhouseSerializationGroup
{
  maxPlants: number;
  maxWaterPlants: number;
  maxDarkPlants: number;
  hasBirdBath: boolean;
  visitingBird: string;
  hasComposter: boolean;
  hasFishStatue: boolean;
  hasMoondial: boolean;
  helper: HelperPetSerializationGroup|null;
}
