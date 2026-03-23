import {PetChangesSerializationGroup} from "./pet-changes.serialization-group";
import { ActivityLogTagSerializationGroup } from "../activity-log-tag.serialization-group";
import { MyPetSerializationGroup } from "../my-pet/my-pet.serialization-group";
import { ToolItemSerializationGroup } from "../public-profile/tool-item.serialization-group";

export interface PetActivitySerializationGroup
{
  entry: string;
  icon: string;
  createdOn: string;
  changes?: PetChangesSerializationGroup;
  interestingness: number;
  tags: ActivityLogTagSerializationGroup[];
  pet?: MyPetSerializationGroup;
  equippedItem?: ToolItemSerializationGroup;
  createdItems: { item: { name: string, image: string } }[];
}
