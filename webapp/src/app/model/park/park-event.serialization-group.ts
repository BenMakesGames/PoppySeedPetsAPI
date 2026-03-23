import {ParkEventParticipantSerializationGroup} from "./park-event-participant.serialization-group";

export interface ParkEventSerializationGroup
{
  id: number;
  participants: ParkEventParticipantSerializationGroup[];
  type: string;
  results: string;
  date: string;
}