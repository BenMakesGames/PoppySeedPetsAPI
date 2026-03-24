export interface ArticleAdminSerializationGroup
{
  id: number;
  imageUrl: string|null;
  title: string;
  body: string;
  designGoals: { name: string, id: number }[];
}