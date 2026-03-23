import { Component, OnDestroy, OnInit } from '@angular/core';
import {ThemeService} from "../../../../shared/service/theme.service";
import { Subscription } from "rxjs";

@Component({
    templateUrl: './behavior.component.html',
    styleUrls: ['./behavior.component.scss'],
    standalone: false
})
export class BehaviorComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Settings - Behavior' };

  multiSelect: string;
  sort: string;
  numberOfLegs: number;
  allowedNumberOfLegs = ThemeService.NumberOfLegs;

  multiSelectSubscription = Subscription.EMPTY;
  sortSubscription = Subscription.EMPTY;
  numberOfLegsSubscription = Subscription.EMPTY;

  constructor(private themeService: ThemeService) {
  }

  ngOnInit() {
    this.sortSubscription = this.themeService.defaultHouseSort.subscribe(s => { this.sort = s; });
    this.numberOfLegsSubscription = this.themeService.numberOfLegs.subscribe(l => { this.numberOfLegs = l; });
    this.multiSelectSubscription = this.themeService.multiSelectWith.subscribe(s => { this.multiSelect = s; });
  }

  ngOnDestroy() {
    this.multiSelectSubscription.unsubscribe();
    this.sortSubscription.unsubscribe();
    this.numberOfLegsSubscription.unsubscribe();
  }

  doSetMultiSelect(s: string)
  {
    this.themeService.setMultiSelectWith(s);
  }

  doSetSort(s: string)
  {
    this.themeService.setDefaultHouseSort(s);
  }

  doUpdateNumberOfLegs(event)
  {
    const numberOfLegs = parseInt(event.target.value);
    this.themeService.setNumberOfLegs(numberOfLegs);
  }
}
