import {ToolSerializationGroup} from "../public-profile/tool.serialization-group";

export interface HollowEarthPetSerializationGroup
{
  name: string;
  colorA: string;
  colorB: string;
  tool: ToolSerializationGroup;
  species: { image: string, handX: number, handY: number, handAngle: number, hatX: number, hatY: number, hatAngle: number, flipX: boolean, handBehind: boolean };
}