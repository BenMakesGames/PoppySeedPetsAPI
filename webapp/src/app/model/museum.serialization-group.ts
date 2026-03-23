export interface MuseumSerializationGroup
{
  user: { id: number, name: string, icon: string };
  item: { id: number, name: string, image: string };
  donatedOn: string;
  comments: string[];
  createdBy: { id: number, name: string, icon: string };
}
