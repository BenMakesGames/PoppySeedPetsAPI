export interface ParkEventParticipantSerializationGroup
{
  id: number;
  owner: { id: number, name: string };
  name: string;
  colorA: string;
  colorB: string;
  species: { image: string };
  spiritCompanion: { image: string };
}