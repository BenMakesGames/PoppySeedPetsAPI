import {HollowEarthPlayerSerializationGroup} from "./hollow-earth-player.serialization-group";
import { HollowEarthTileSerializationGroup } from "./hollow-earth-tile.serialization-group";

export interface HollowEarthSerializationGroup
{
  player: HollowEarthPlayerSerializationGroup;
  map: HollowEarthTileSerializationGroup[];
  dice: { item: string, image: string, size: number, quantity: number }[];
}