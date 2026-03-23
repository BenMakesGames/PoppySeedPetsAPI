import {Injectable} from "@angular/core";

import {Observable} from "rxjs";
import {filter, map, take} from "rxjs/operators";
import {UserDataService} from "../service/user-data.service";

@Injectable({
  providedIn: 'root'
})
export class AnyGuard
{
  constructor(private userData: UserDataService)
  {

  }

  canActivate(): Observable<boolean>
  {
    return this.userData.user.pipe(
      filter(u => u !== UserDataService.UNLOADED),
      map(u => true),
      take(1)
    );
  }
}
