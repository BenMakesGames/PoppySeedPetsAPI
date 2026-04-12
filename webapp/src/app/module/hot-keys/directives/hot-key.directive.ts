/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
