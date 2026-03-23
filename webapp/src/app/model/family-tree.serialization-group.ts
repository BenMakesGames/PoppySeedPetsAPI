import { PetRelationship } from "./my-pet/pet-friend.serialization-group";

export interface FamilyTreeSerializationGroup
{
  grandparents: PetRelationship[];
  parents: PetRelationship[];
  siblings: PetRelationship[];
  children: PetRelationship[];

  spiritParent: { id: number, name: string, image: string }|null;
  spiritGrandparents: { id: number, name: string, image: string }[];
}