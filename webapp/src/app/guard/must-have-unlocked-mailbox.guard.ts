import {Injectable} from "@angular/core";
import { Router } from "@angular/router";
import {Observable} from "rxjs";
import {UserDataService} from "../service/user-data.service";
import { mustHaveUnlocked } from "./must-have-unlocked";

@Injectable({
  providedIn: 'root'
})
export class MustHaveUnlockedMailboxGuard 
{
  constructor(private userData: UserDataService, private router: Router)
  {

  }

  canActivate(): Observable<boolean>
  {
    return mustHaveUnlocked(this.userData, this.router, 'Mailbox');
  }
}
