import {ToolItemSerializationGroup} from "../public-profile/tool-item.serialization-group";
import {HatItemSerializationGroup} from "../public-profile/hat-item.serialization-group";
import {ToolItemGripSerializationGroup} from "../public-profile/tool-item-grip.serialization-group";
import {FoodSerializationGroup} from "../food.serialization-group";

export interface MyInventoryItemSerializationGroup
{
  id: number;
  food: FoodSerializationGroup;
  image: string;
  name: string;
  description: string;
  useActions: any[];
  tool: ToolItemSerializationGroup;
  hat: HatItemSerializationGroup;
  greenhouseType?: string;
  isFertilizer: boolean;
  isFlammable: boolean;
  isTreasure: boolean;
  recycleValue: number;
  museumPoints?: number;
  enchants: { effects: ToolItemGripSerializationGroup, name: string, isSuffix: boolean }|null;
  spice: { effects: FoodSerializationGroup, name: string, isSuffix: boolean }|null;
  hollowEarthTileCard: { name: string, type: { name: string, article: string } };
  itemGroups: { name: string }[];
}
