import {GreenhousePlantTypeEnum} from "../greenhouse-plant-type.enum";

export interface GreenhousePlantSerializationGroup
{
  id: number;
  plant: { name: string, type: GreenhousePlantTypeEnum };
  lastInteraction: string;
  canNextInteract: string;
  isAdult: boolean;
  image: string;
  progress: number;
  previousProgress: number;
  ordinal: number;
  pollinators: null|'bees'|'butterflies';
}
