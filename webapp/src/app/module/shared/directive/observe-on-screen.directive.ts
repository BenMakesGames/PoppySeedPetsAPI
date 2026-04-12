/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {AfterViewInit, Directive, ElementRef, EventEmitter, OnDestroy, OnInit, Output} from '@angular/core';

@Directive({
  standalone: true,
  selector: '[appObserveOnScreen]'
})
export class ObserveOnScreenDirective implements OnDestroy, OnInit, AfterViewInit {

  @Output() isOnScreen = new EventEmitter<boolean>();

  private observer: IntersectionObserver|undefined;

  constructor(private element: ElementRef) {
  }

  ngOnInit() {
    this.createObserver();
  }

  ngAfterViewInit() {
    this.observer.observe(this.element.nativeElement);
  }

  ngOnDestroy() {
    if (this.observer) {
      this.observer.disconnect();
      this.observer = undefined;
    }
  }

  private createObserver() {
    this.observer = new IntersectionObserver(entries => {
      this.isOnScreen.emit(entries[0].intersectionRatio === 1);
    }, {
      threshold: [0, 1]
    });
  }
}
