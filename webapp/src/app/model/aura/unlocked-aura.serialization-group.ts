import { MyAuraSerializationGroup } from "./my-aura.serialization-group";

export interface UnlockedAuraSerializationGroup
{
  id: number|null;
  unlockedOn: string|null;
  comment: string|null;
  name: string,
  aura: MyAuraSerializationGroup,
}