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
