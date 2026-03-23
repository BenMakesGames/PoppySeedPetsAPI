export interface ArticleSerializationGroup
{
  id: number;
  imageUrl: string|null;
  title: string;
  body: string;
  createdOn: string;
  author: { id: number, name: string };
  designGoals: { id: number, name: string }[];
}