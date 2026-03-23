import { ToolSerializationGroup } from "./public-profile/tool.serialization-group";

export interface HelperPetSerializationGroup
{
  id: number;
  name: string;
  colorA: string;
  colorB: string;
  tool: ToolSerializationGroup;
  hat: any;
  species: { image: string, handX: number, handY: number, handAngle: number, hatX: number, hatY: number, hatAngle: number, flipX: boolean, handBehind: boolean, pregnancyStyle: number, eggImage: string, family: string, name: string };
  merits: { name: string }[];
}