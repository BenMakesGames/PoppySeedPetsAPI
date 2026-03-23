import { QueryStringService } from "../../service/query-string.service";

export interface PetSearchModel
{
  name: string;
  nameExactMatch: boolean;
  species: number|null;
  merit: number|null;
  toolOrHat: number|null;
  isPregnant: boolean|null;
  orderBy: string|null;
}

export function CreatePetSearchModel(): PetSearchModel {
  return {
    name: '',
    nameExactMatch: false,
    species: null,
    merit: null,
    toolOrHat: null,
    isPregnant: null,
    orderBy: null,
  };
}

export function CreatePetSearchModelFromQueryObject(query: any): PetSearchModel
{
  let search: PetSearchModel = {
    name: '',
    nameExactMatch: false,
    species: null,
    merit: null,
    toolOrHat: null,
    isPregnant: null,
    orderBy: null,
  };

  if('name' in query) search.name = query.name.toString().trim();
  if('nameExactMatch' in query) search.nameExactMatch = QueryStringService.parseBool(query.nameExactMatch, false);
  if('species' in query) search.species = QueryStringService.parseNullableInt(query.species);
  if('merit' in query) search.merit = QueryStringService.parseNullableInt(query.merit);
  if('toolOrHat' in query) search.toolOrHat = QueryStringService.parseNullableInt(query.toolOrHat);
  if('isPregnant' in query) search.isPregnant = QueryStringService.parseNullableBool(query.isPregnant);
  if('orderBy' in query) search.orderBy = query.orderBy.toString().trim();

  return search;
}

export function CreateRequestDtoFromPetSearchModel(search: PetSearchModel): any
{
  let filter: any = {};

  if(search.name && search.name.trim().length > 0) filter.name = search.name.trim();
  if(search.nameExactMatch) filter.nameExactMatch = search.nameExactMatch;
  if(search.species && search.species > 0) filter.species = search.species;
  if(search.merit && search.merit > 0) filter.merit = search.merit;
  if(search.toolOrHat && search.toolOrHat > 0) filter.toolOrHat = search.toolOrHat;
  if(search.isPregnant !== null) filter.isPregnant = search.isPregnant;
  if(search.orderBy && search.orderBy.trim().length > 0) filter.orderBy = search.orderBy.trim();

  return filter;
}
