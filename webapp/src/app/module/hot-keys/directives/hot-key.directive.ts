import { Directive, ElementRef, Input, OnDestroy, OnInit } from '@angular/core';
import { Subscription } from 'rxjs';
import { HotKeysService } from "../../../service/hot-keys.service";
import { Router } from "@angular/router";

@Directive({
    selector: '[appHotKey]',
    standalone: false
})
export class HotKeyDirective implements OnInit, OnDestroy {

  @Input('appHotKey') hotKey: string;

  subscription = Subscription.EMPTY;

  constructor(private hotKeys: HotKeysService, private element: ElementRef, private router: Router) {
  }

  ngOnInit(): void {
    this.hotKeys.addShortcut(this.hotKey).subscribe(() => {
      // TODO: this works for hrefs which don't go off-site; not so sure it works for other things, but
      // for PSP's use-case, that's probs fine...
      if(this.element.nativeElement.getAttribute('href'))
        this.router.navigateByUrl(this.element.nativeElement.getAttribute('href'));
      else
        this.element.nativeElement.click();
    });
  }

  ngOnDestroy(): void {
    this.subscription.unsubscribe();
  }
}
