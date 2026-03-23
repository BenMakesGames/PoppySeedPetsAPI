export interface HollowEarthTradeSerializationGroup
{
  id: string;
  item: { name: string, image: string };
  cost: HollowEarthTradeCost;
  maxQuantity: number;
}

export interface HollowEarthTradeCost
{
  jade?: number;
  incense?: number;
  amber?: number;
  salt?: number;
  fruit?: number;
}