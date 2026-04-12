/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, input, OnDestroy, OnInit } from '@angular/core';
import { DatePipe } from "@angular/common";
import { ThemeService } from "../../service/theme.service";
import { Subscription } from "rxjs";

@Component({
    selector: 'app-psp-time',
    imports: [
        DatePipe
    ],
    templateUrl: './psp-time.component.html',
    styleUrl: './psp-time.component.scss'
})
export class PspTimeComponent implements OnInit, OnDestroy {
  date = input.required<Date|string>();

  timeFormatSubscription = Subscription.EMPTY;
  timeFormat = '12hr';

  constructor(private readonly themeService: ThemeService) {
  }

  ngOnInit(): void {
    this.timeFormatSubscription = this.themeService.timeFormat.subscribe(v => this.timeFormat = v);
  }

  ngOnDestroy(): void {
    this.timeFormatSubscription.unsubscribe();
  }
}
