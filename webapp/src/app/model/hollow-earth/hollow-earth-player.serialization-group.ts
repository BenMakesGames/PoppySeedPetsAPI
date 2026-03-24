import {HollowEarthPetSerializationGroup} from "./hollow-earth-pet.serialization-group";

export interface HollowEarthPlayerSerializationGroup
{
  currentTile: { x: number, y: number, moveDirection: string };
  movesRemaining: number;
  chosenPet: HollowEarthPetSerializationGroup;
  action: any;
  jade: number;
  incense: number;
  salt: number;
  amber: number;
  fruit: number;
  showGoods: boolean;
}