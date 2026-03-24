import { Pipe, PipeTransform } from '@angular/core';
import {
  isFeatureUnlocked,
  MyAccountSerializationGroup
} from "../../../model/my-account/my-account.serialization-group";

@Pipe({
  standalone: true,
  name: 'hasUnlockedFeature'
})
export class HasUnlockedFeaturePipe implements PipeTransform {

  transform(user: MyAccountSerializationGroup|null|undefined, feature: string): boolean {
    return user && isFeatureUnlocked(user, feature);
  }

}
