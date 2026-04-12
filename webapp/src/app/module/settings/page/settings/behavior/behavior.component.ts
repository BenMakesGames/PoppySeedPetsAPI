/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
