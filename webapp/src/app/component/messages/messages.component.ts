import {Component, OnDestroy, OnInit} from '@angular/core';
import {Subscription} from "rxjs";
import {MessagesService} from "../../service/messages.service";
import {PetActivitySerializationGroup} from "../../model/pet-activity-logs/pet-activity.serialization-group";

@Component({
  selector: 'app-messages',
  template: `
    @if(activities.length > 0)
    {
      <div [class.minimized]="hidden" (click)="doHideOrShow()" class="app-messages">
        @if(hidden)
        {
          <div class="expand-me"><i class="fa-solid fa-caret-up"></i><i class="fa-solid fa-caret-up"></i><i class="fa-solid fa-caret-up"></i></div>
        }
        @else
        {
          <button type="button" class="link-text close" (click)="doClearMessages()"><i class="fa-regular fa-xmark"></i></button>
          <div class="activity">
            @if(activities.length > 0)
            {
              <ul>
                @for(activity of activities; track $index)
                {
                  <li>
                    @if(activity.icon)
                    {
                      <img [src]="'/assets/images/' + activity.icon + '.svg'" class="activity-log-icon" />
                    }
                    <markdown [data]="activity.entry" />
                  </li>
                }
              </ul>
            }
            <div class="fade-out"></div>
          </div>
        }
      </div>
    }
  `,
  styleUrls: ['./messages.component.scss'],
  host: {
    '(body:mousedown)': 'doHostMouseDown($event)', // "click" doesn't register properly on mobile; "mousedown" does! (weird!)
  },
  standalone: false
})
export class MessagesComponent implements OnInit, OnDestroy {

  private activitySubscription = Subscription.EMPTY;
  public activities: PetActivitySerializationGroup[] = [];

  hidden = true;

  constructor(private messages: MessagesService) {
  }

  doHostMouseDown(event: any)
  {
    // if the messages are already hidden, we don't need to think any more about it:
    if(this.hidden || this.activities.length === 0)
      return;

    let target = event.target;

    // travel up the DOM, until you get to the <body>, or .app-messages container
    while(target && target.nodeName.toLowerCase() !== 'body')
    {
      // if the click was inside the app-messages, then we DON'T want to hide it (at least not with this logic)
      if(target.classList.contains('app-messages'))
        return;

      target = target.parentNode;
    }

    // if we got all the way up to the <body>, then hide!
    this.hidden = true;
  }

  doHideOrShow()
  {
    this.hidden = !this.hidden;
  }

  doClearMessages()
  {
    this.messages.clearMessages();
  }

  ngOnInit()
  {
    this.activitySubscription = this.messages.activity.subscribe({
      next: (activities: PetActivitySerializationGroup[]) => {
        this.hidden = false;

        this.activities = activities;
      }
    });
  }

  ngOnDestroy()
  {
    this.activitySubscription.unsubscribe();
  }
}
