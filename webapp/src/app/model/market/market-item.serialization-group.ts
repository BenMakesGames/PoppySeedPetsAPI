export interface MarketItemSerializationGroup
{
  item: { id: number, name: string, nameWithArticle: string, image: string },
  enchantment: { id: number, name: string, isSuffix: boolean };
  spice: { id: number, name: string, isSuffix: boolean };
  minimumSellPrice: number;
}
