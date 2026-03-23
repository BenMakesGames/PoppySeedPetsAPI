import {MyPetSerializationGroup} from "./my-pet/my-pet.serialization-group";

export interface DialogResponseModel
{
  inventoryChanged?: boolean;
  newPet?: MyPetSerializationGroup;
  updatedPet?: MyPetSerializationGroup;
  removedPet?: MyPetSerializationGroup;
}
