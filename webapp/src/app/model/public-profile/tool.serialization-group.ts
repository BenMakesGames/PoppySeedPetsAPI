import {ToolItemSerializationGroup} from "./tool-item.serialization-group";
import { ToolItemGripSerializationGroup } from "./tool-item-grip.serialization-group";
import { MyAuraSerializationGroup } from "../aura/my-aura.serialization-group";

export interface ToolSerializationGroup
{
  id: number;
  item: ToolItemSerializationGroup;
  illusion: { image: string, name: string }|null;
  enchantment: { effects: ToolItemGripSerializationGroup, name: string, isSuffix: boolean, aura: MyAuraSerializationGroup|null }|null;
}
