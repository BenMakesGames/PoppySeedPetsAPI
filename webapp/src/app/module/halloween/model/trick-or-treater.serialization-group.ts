import {PetPublicProfileSerializationGroup} from "../../../model/public-profile/pet-public-profile.serialization-group";
import {MyInventorySerializationGroup} from "../../../model/my-inventory/my-inventory.serialization-group";

export interface TrickOrTreaterSerializationGroup
{
  trickOrTreater: PetPublicProfileSerializationGroup;
  nextTrickOrTreater: string;
  candy: MyInventorySerializationGroup[];
  totalCandyGiven: number;
}
