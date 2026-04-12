/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Injectable} from "@angular/core";
import { Router } from "@angular/router";
import {Observable} from "rxjs";
import {UserDataService} from "../service/user-data.service";
import { mustHaveUnlocked } from "./must-have-unlocked";

@Injectable({
  providedIn: 'root'
})
export class MustHaveUnlockedLibraryGuard
{
  constructor(private userData: UserDataService, private router: Router)
  {

  }

  canActivate(): Observable<boolean>
  {
    return mustHaveUnlocked(this.userData, this.router, 'Library');
  }
}
