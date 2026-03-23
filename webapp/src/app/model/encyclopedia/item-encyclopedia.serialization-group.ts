import {ToolItemGripSerializationGroup} from "../public-profile/tool-item-grip.serialization-group";
import {FoodSerializationGroup} from "../food.serialization-group";

export interface ItemEncyclopediaSerializationGroup
{
  id: number;
  food: FoodSerializationGroup;
  image: string;
  name: string;
  nameWithArticle: string;
  description: string;
  useActions: string[][];
  tool: {
    modifiers: string[];
  };
  hat: any;
  greenhouseType: string|null;
  isFlammable: boolean;
  isFertilizer: boolean;
  isTreasure: boolean;
  recycleValue: number;
  enchants: { effects: ToolItemGripSerializationGroup, name: string, isSuffix: boolean }|null;
  spice: { effects: FoodSerializationGroup, name: string, isSuffix: boolean }|null;
  hollowEarthTileCard: { name: string, type: { name: string, article: string } };
  itemGroups: {name: string}[];
}
