export interface PetGroupDetailsSerializationGroup
{
  id: number;
  type: number;
  name: string;
  createdOn: string;
  lastMetOn: string;
  makesStuff: boolean;
  numberOfProducts: number;
  members: {
    id: number;
    name: string;
    colorA: string;
    colorB: string;
    species: { image: string; };
  }[];
}