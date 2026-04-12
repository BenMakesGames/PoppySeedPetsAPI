/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
