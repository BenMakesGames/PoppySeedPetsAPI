export interface PetFriendSerializationGroup
{
  relationship: PetRelationship,
  metDescription: string,
  metOn: string,
  lastMet: string,
  currentRelationship: string,
  relationshipWanted: string|null,
  commitment: number,
}

export interface PetRelationship
{
  id: number,
  name: string,
  colorA: string,
  colorB: string,
  species: {
    image: string,
    flipX: boolean,
    pregnancyStyle: number,
    eggImage: string,
  },
  pregnancy: { eggColor: string }|null,
  scale: number,
}