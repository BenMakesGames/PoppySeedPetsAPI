export interface DragonTreasureSerializationGroup
{
  id: number;
  sellPrice: number|null;
  enchantment: { name: string, isSuffix: boolean };
  spice: { name: string, isSuffix: boolean };
  item: {
    name: string;
    image: string;
    treasure: {
      silver: number;
      gold: number;
      gems: number;
    }
  }
}
