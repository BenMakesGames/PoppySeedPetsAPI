export interface KnownRecipeSerializationGroup
{
  id: number;
  name: string;
  ingredients: { item: { name: string, image: string }, quantity: number, available: number }[];
  makes: { quantity: number, item: { name: string, image: string } }[];
  canPrepare: boolean;
}
