/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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