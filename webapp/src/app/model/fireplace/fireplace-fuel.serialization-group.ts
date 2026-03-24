export interface FireplaceFuelSerializationGroup
{
  id: number;
  item: { image: string, name: string, fuelRating: number };
  holder: { id: number };
  wearer: { id: number };
  sellPrice: number;
}