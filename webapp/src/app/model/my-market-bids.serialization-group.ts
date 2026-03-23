export interface MyMarketBidsSerializationGroup
{
  id: number;
  item: { name: string, image: string };
  bid: number;
  quantity: number;
  createdOn: string;
  targetLocation: number;
}
