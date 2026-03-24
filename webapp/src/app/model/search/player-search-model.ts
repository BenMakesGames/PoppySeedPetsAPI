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
