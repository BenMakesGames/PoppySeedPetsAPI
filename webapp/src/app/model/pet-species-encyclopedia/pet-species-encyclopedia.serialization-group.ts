export interface PetSpeciesEncyclopediaSerializationGroup
{
  id: number;
  name: string;
  image: string;
  family: string;
  description: string;
  physicalDescription: string|null;
  availableAtSignup: boolean;
  availableFromBreeding: boolean;
  availableFromPetShelter: boolean;
  pregnancyStyle: number;
  eggImage: string;
  flipX: boolean;
  numberOfPets: number;
}
