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
