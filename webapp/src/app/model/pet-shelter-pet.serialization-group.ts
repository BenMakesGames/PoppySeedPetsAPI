export interface PetShelterPetSerializationGroup
{
  id: string;
  name: string;
  species: { name: string, image: string };
  colorA: string;
  colorB: string;
  label: string|null;
}
