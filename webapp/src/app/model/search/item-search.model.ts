import { QueryStringService } from "../../service/query-string.service";

export interface ItemSearchModel
{
  name: string;
  nameExactMatch: boolean;
  edible: boolean|null;
  spice: boolean|null;
  candy: boolean|null;
  foodFlavors: string[];
  equipable: boolean|null;
  equipStats: string[];
  bonus: boolean|null;
  aHat: boolean|null;
  hasDonated: boolean|null;
  isFuel: boolean|null;
  isFertilizer: boolean|null;
  isTreasure: boolean|null;
  isRecyclable: boolean|null;
  itemGroup: string|null;
}

export function CreateItemSearchModel(): ItemSearchModel {
  return {
    name: '',
    nameExactMatch: false,
    edible: null,
    spice: null,
    candy: null,
    foodFlavors: [],
    equipable: null,
    equipStats: [],
    bonus: null,
    aHat: null,
    hasDonated: null,
    isFuel: null,
    isFertilizer: null,
    isTreasure: null,
    isRecyclable: null,
    itemGroup: null,
  };
}

export function CreateItemSearchModelFromQueryObject(query: any): ItemSearchModel
{
  let search: ItemSearchModel = CreateItemSearchModel();

  if('name' in query) search.name = query.name.toString().trim();
  if('nameExactMatch' in query) search.nameExactMatch = QueryStringService.parseBool(query.nameExactMatch, false);
  if('edible' in query) search.edible = QueryStringService.parseNullableBool(query.edible);
  if('candy' in query) search.candy = QueryStringService.parseNullableBool(query.candy);
  if('spice' in query) search.spice = QueryStringService.parseNullableBool(query.spice);
  if('foodFlavors' in query) search.foodFlavors = QueryStringService.parseArray(query.foodFlavors);
  if('equipable' in query) search.equipable = QueryStringService.parseNullableBool(query.equipable);
  if('equipStats' in query) search.equipStats = QueryStringService.parseArray(query.equipStats);
  if('bonus' in query) search.bonus = QueryStringService.parseNullableBool(query.bonus);
  if('aHat' in query) search.aHat = QueryStringService.parseNullableBool(query.aHat);
  if('hasDonated' in query) search.hasDonated = QueryStringService.parseNullableBool(query.hasDonated);
  if('isFuel' in query) search.isFuel = QueryStringService.parseNullableBool(query.isFuel);
  if('isFertilizer' in query) search.isFertilizer = QueryStringService.parseNullableBool(query.isFertilizer);
  if('isTreasure' in query) search.isTreasure = QueryStringService.parseNullableBool(query.isTreasure);
  if('isRecyclable' in query) search.isRecyclable = QueryStringService.parseNullableBool(query.isRecyclable);
  if('itemGroup' in query) search.itemGroup = query.itemGroup.toString().trim();

  return search;
}

export function CreateRequestDtoFromItemSearchModel(search: ItemSearchModel): any
{
  let filter: any = {};

  if(search.name && search.name.trim().length > 0) filter.name = search.name.trim();
  if(search.nameExactMatch) filter.nameExactMatch = search.nameExactMatch;
  if(search.aHat !== null) filter.aHat = search.aHat;
  if(search.equipable !== null) filter.equipable = search.equipable;
  if(search.equipStats !== null && search.equipStats.length > 0) filter.equipStats = search.equipStats;
  if(search.bonus !== null) filter.bonus = search.bonus;
  if(search.edible !== null) filter.edible = search.edible;
  if(search.spice !== null) filter.spice = search.spice;
  if(search.candy !== null) filter.candy = search.candy;
  if(search.foodFlavors !== null && search.foodFlavors.length > 0) filter.foodFlavors = search.foodFlavors;
  if(search.hasDonated !== null) filter.hasDonated = search.hasDonated;
  if(search.isFuel !== null) filter.isFuel = search.isFuel;
  if(search.isFertilizer !== null) filter.isFertilizer = search.isFertilizer;
  if(search.isTreasure !== null) filter.isTreasure = search.isTreasure;
  if(search.isRecyclable !== null) filter.isRecyclable = search.isRecyclable;
  if(search.itemGroup !== null) filter.itemGroup = search.itemGroup;

  return filter;
}
