/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Injectable, Injector} from '@angular/core';
import {BehaviorSubject, Subject} from "rxjs";
import {MyAccountSerializationGroup} from "../model/my-account/my-account.serialization-group";
import {ActivatedRoute, Router, RouterStateSnapshot} from "@angular/router";
import {MyPetSerializationGroup} from "../model/my-pet/my-pet.serialization-group";
import {MyInventorySerializationGroup} from "../model/my-inventory/my-inventory.serialization-group";

@Injectable({
  providedIn: 'root'
})
export class UserDataService {

  static readonly UNLOADED: any = { id: null };

  user = new BehaviorSubject<MyAccountSerializationGroup>(UserDataService.UNLOADED);

  userPetsChanged = new Subject<MyPetSerializationGroup[]|null>();
  userInventoryChanged = new Subject<MyInventorySerializationGroup[]|null>();

  public constructor(private activatedRoute: ActivatedRoute, private injector: Injector, private router: Router)
  {
  }

  public updateUser(u: MyAccountSerializationGroup)
  {
    if(u === null && this.user.value === null) return;

    this.user.next(u);

    this.forceRunAuthGuard();
  }

  public getUserShortName(): string
  {
    if(!this.user.value) return null;

    return this.user.value.name.replace(/[ ,].*/, '');
  }

  // from https://stackoverflow.com/questions/45680250/angular2-how-to-reload-page-with-router-recheck-canactivate
  private forceRunAuthGuard()
  {
    // gets current route
    if (this.activatedRoute.root.children.length === 0) return;

    const currentRoute = this.activatedRoute.root.children[0];

    // gets first guard class
    if(!currentRoute.snapshot.routeConfig.canActivate || currentRoute.snapshot.routeConfig.canActivate.length === 0) return;

    const authGuardClass = currentRoute.snapshot.routeConfig.canActivate[0];

    // injects guard
    const authGuard = this.injector.get(authGuardClass);

    // makes custom RouterStateSnapshot object
    const routerStateSnapshot: RouterStateSnapshot = Object.assign({}, currentRoute.snapshot, { url: this.router.url });

    // runs canActivate
    const canActivate = authGuard.canActivate(currentRoute.snapshot, routerStateSnapshot);

    // it might be a subscription...
    try
    {
      canActivate.subscribe();
    }
    catch(e)
    {
      console.log('Exception subscribing to canAcivate!');
      console.log(e);
    }
  }

}
