import {PetActivitySerializationGroup} from "./pet-activity-logs/pet-activity.serialization-group";
import {MyAccountSerializationGroup} from "./my-account/my-account.serialization-group";

export interface ApiResponseModel<T>
{
  success: boolean;
  errors?: string[];
  user?: MyAccountSerializationGroup;
  data: T;
  activity?: PetActivitySerializationGroup[];
  event: any;
  reloadPets?: boolean;
  reloadInventory?: boolean;
}
