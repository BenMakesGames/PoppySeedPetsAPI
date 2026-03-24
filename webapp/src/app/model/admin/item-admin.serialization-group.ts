export interface ItemAdminSerializationGroup
{
  id: number;
  name: string;
  food: { food: number, love: number, junk: number, whack: number };
  description: string;
  image: string;
  useActions: string[][];
}