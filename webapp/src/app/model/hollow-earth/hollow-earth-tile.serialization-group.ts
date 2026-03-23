export interface HollowEarthTileSerializationGroup
{
  id: number;
  x: number;
  y: number;
  name: string|null;
  image: string|null;
  fixed: boolean;
  types: string[];
  availableGoods: HollowEarthGood[]|null;
  goodsSide: HollowEarthDirection|null;
  selectedGoods: HollowEarthGood|null;
  isTradingDepot: boolean;
  author: { name: string, id: number|null }|null;
}

export enum HollowEarthDirection
{
  N = 'N',
  E = 'E',
  S = 'S',
  W = 'W'
}

export enum HollowEarthGood
{
  Jade = 'jade',
  Incense = 'incense',
  Salt = 'salt',
  Amber = 'amber',
  Fruit = 'fruit'
}
