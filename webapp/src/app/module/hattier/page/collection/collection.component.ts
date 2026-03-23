import { Component, OnDestroy, OnInit } from '@angular/core';
import { Subscription } from "rxjs";
import { ApiService } from "../../../shared/service/api.service";
import { AvailableStylesResponse } from "../../model/available-styles-response";

@Component({
    selector: 'app-collection',
    templateUrl: './collection.component.html',
    styleUrls: ['./collection.component.scss'],
    standalone: false
})
export class CollectionComponent implements OnInit, OnDestroy {

  pageMeta = { title: 'The Hattier' };

  hattierDialog = '';
  myStylesSubscription = Subscription.EMPTY;
  myStyles: any[] = [];

  isOctober = false;

  constructor(private api: ApiService) { }

  ngOnInit(): void
  {
    this.myStylesSubscription = this.api.get<AvailableStylesResponse>('/hattier/unlockedStyles').subscribe({
      next: r => {
        const totalStyles = r.data.available.length;
        this.myStyles = r.data.available.filter(s => s.unlockedOn);

        if(this.myStyles.length / totalStyles >= 0.85)
          this.hattierDialog = 'I must thank you for again for all the unique stylings your pets have come to me with. I never could have imagined some of these on my own, even _in_ my wildest dreams...';
        else if(this.myStyles.length > 6)
          this.hattierDialog = 'Your pets have been very inspiring; it\'s wonderful to see what they come up with!';
        else
          this.hattierDialog = 'I know there are more styles out there to find... I\'ve _dreamt_ of them! They were so vivid, and yet, when I awoke, their appearance vanished from my mind\'s eye...';
      }
    });

    this.isOctober = (new Date()).getMonth() === 9;
  }

  ngOnDestroy() {
    this.myStylesSubscription.unsubscribe();
  }

}
