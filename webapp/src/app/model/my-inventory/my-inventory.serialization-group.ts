import {MyInventoryItemSerializationGroup} from "./my-inventory-item.serialization-group";
import {ToolItemGripSerializationGroup} from "../public-profile/tool-item-grip.serialization-group";
import {FoodSerializationGroup} from "../food.serialization-group";
import { MyAuraSerializationGroup } from "../aura/my-aura.serialization-group";

export interface MyInventorySerializationGroup
{
  id: number;
  comments: string[];
  item: MyInventoryItemSerializationGroup;
  illusion: { image: string, name: string }|null;
  createdBy: { id: number, name: string };
  createdOn: string;
  modifiedOn: string;
  selected?: boolean;
  sellPrice: number|null;
  lockedToOwner: boolean;
  enchantment: { effects: ToolItemGripSerializationGroup, name: string, isSuffix: boolean, aura: MyAuraSerializationGroup|null }|null;
  enchantmentHue: number|null;
  spice: { effects: FoodSerializationGroup, name: string, isSuffix: boolean }|null;
  isUpgrade?: boolean;
}
