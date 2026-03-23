import { filter, map, take } from "rxjs/operators";
import { UserDataService } from "../service/user-data.service";
import { Router } from "@angular/router";
import { isFeatureUnlocked } from "../model/my-account/my-account.serialization-group";

export function mustHaveUnlocked(userData: UserDataService, router: Router, featureName: string)
{
  return userData.user.pipe(
    filter(u => u !== UserDataService.UNLOADED),
    map(u => {
      if (u === null || !isFeatureUnlocked(u, featureName))
      {
        router.navigateByUrl('/');
        return false;
      }
      else
        return true;
    }),
    take(1)
  );
}