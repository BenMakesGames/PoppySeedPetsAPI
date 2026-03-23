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
