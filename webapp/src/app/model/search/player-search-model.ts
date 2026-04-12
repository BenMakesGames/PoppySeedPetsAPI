/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { QueryStringService } from "../../service/query-string.service";

export interface PlayerSearchModel
{
  name: string;
  nameExactMatch: boolean;
}

export function CreatePlayerSearchModel(): PlayerSearchModel {
  return {
    name: '',
    nameExactMatch: false,
  };
}

export function CreatePlayerSearchModelFromQueryObject(query: any): PlayerSearchModel
{
  let search: PlayerSearchModel = {
    name: '',
    nameExactMatch: false,
  };

  if('name' in query) search.name = query.name.toString().trim();
  if('nameExactMatch' in query) search.nameExactMatch = QueryStringService.parseBool(query.nameExactMatch, false);

  return search;
}

export function CreateRequestDtoFromPlayerSearchModel(search: PlayerSearchModel): any
{
  let filter: any = {};

  if(search.name && search.name.trim().length > 0) filter.name = search.name.trim();
  if(search.nameExactMatch) filter.nameExactMatch = search.nameExactMatch;

  return filter;
}
