import {PetGroupTypeEnum} from "./pet-group-type.enum";

export interface PetGroupIndexSerializationGroup
{
  id: number;
  type: PetGroupTypeEnum;
  name: string;
  createdOn: string;
  lastMetOn: string;
}
