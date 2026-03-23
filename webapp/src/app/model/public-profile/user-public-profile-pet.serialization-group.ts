import {ToolSerializationGroup} from "./tool.serialization-group";
export interface UserPublicProfilePetSerializationGroup
{
  id: number;
  name: string;
  colorA: string;
  colorB: string;
  tool?: ToolSerializationGroup;
  species: { image: string };
}