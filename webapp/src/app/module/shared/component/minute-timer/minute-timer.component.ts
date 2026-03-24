import { Component, Input, OnDestroy, OnInit } from '@angular/core';

@Component({
    selector: 'app-minute-timer',
    imports: [],
    templateUrl: './minute-timer.component.html',
    styleUrl: './minute-timer.component.scss'
})
export class MinuteTimerComponent implements OnInit, OnDestroy {
  @Input({ required: true }) minutesRemaining: number;

  seconds = 60;

  interval: number|undefined;

  ngOnInit() {
    if(this.minutesRemaining <= 0)
      this.seconds = 0;

    this.interval = window.setInterval(() => {
      if(this.minutesRemaining <= 0)
      {
        this.seconds = 0;
        return;
      }

      this.seconds--;

      if(this.seconds === 0)
      {
        this.seconds = 60;
        this.minutesRemaining--;
      }
    }, 1000);
  }

  ngOnDestroy() {
    if(this.interval)
      clearInterval(this.interval);
  }
}
