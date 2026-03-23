import {Injectable} from "@angular/core";
import { Router } from "@angular/router";
import {Observable} from "rxjs";
import {filter, map, take} from "rxjs/operators";
import {UserDataService} from "../service/user-data.service";

@Injectable({
  providedIn: 'root'
})
export class MustBeLoggedOutGuard 
{
  constructor(private userData: UserDataService, private router: Router)
  {

  }

  canActivate(): Observable<boolean>
  {
    return this.userData.user.pipe(
      filter(u => u !== UserDataService.UNLOADED),
      map(u => {
        if(u !== null)
        {
          this.router.navigate([ '/home' ]);
          return false;
        }
        else
          return true;
      }),
      take(1)
    );
  }
}
