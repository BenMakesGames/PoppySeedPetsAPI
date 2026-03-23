import {ToolSerializationGroup} from "./tool.serialization-group";
import {PetGroupTypeEnum} from "../pet-group-type.enum";

export interface PetPublicProfileSerializationGroup
{
  id: number;
  owner: { id: number, name: string, icon: string };
  name: string;
  level: number;
  colorA: string;
  colorB: string;
  hat?: ToolSerializationGroup;
  birthDate: string;
  species: { image: string };
  costume: string;
  maximumFriends: number;
  groups: { id: number, name: string, type: PetGroupTypeEnum, createdOn: string, memberCount: number }[];
  guildMembership: { guild: { name: string, id: number }, rank: string, joinedOn: string }|null;
  badges: { badge: string, dateAcquired: string }[];
}
