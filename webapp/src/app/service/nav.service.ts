/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Inject, Injectable, DOCUMENT } from '@angular/core';
import { BehaviorSubject, Subject } from "rxjs";
import {NavigationEnd, Router} from "@angular/router";


@Injectable({
  providedIn: 'root'
})
export class NavService {

  public showNav = new BehaviorSubject<string>('closed');
  public showLogIn = new Subject<void>();
  public disableHeaderShadow = new BehaviorSubject<boolean>(false);

  constructor(private router: Router, @Inject(DOCUMENT) private document: Document) {
    router.events.subscribe(v => {
      if(v instanceof NavigationEnd)
        this.disableHeaderShadow.next(false);
    });
  }

  public openNav()
  {
    this.showNav.next('open');
    this.document.body.classList.add('scroll-lock');
  }

  public closeNav()
  {
    this.showNav.next('closed');
    this.document.body.classList.remove('scroll-lock');
  }

  public toggleNav()
  {
    if(this.showNav.value !== 'closed')
      this.closeNav();
    else
      this.openNav();
  }
}
