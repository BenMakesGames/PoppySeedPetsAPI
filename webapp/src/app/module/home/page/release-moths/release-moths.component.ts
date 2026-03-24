import { Component, OnInit } from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {ActivatedRoute, Router} from "@angular/router";

@Component({
    templateUrl: './release-moths.component.html',
    styleUrls: ['./release-moths.component.scss'],
    standalone: false
})
export class ReleaseMothsComponent implements OnInit {
  inventoryId: number;
  location: number;
  locationDescription: string = '';
  numberToSend: number = 1;
  now: Date = new Date();
  route: string;

  mothsAvailable: number|null = null;

  sendingMoths = false;

  constructor(private api: ApiService, private router: Router, private activatedRoute: ActivatedRoute)
  {

  }

  ngOnInit()
  {
    this.inventoryId = parseInt(this.activatedRoute.snapshot.paramMap.get('id'));

    this.api.get<{ location: number, quantity: number }>('/item/moth/getQuantity/' + this.inventoryId).subscribe({
      next: r => {
        this.location = r.data.location;
        this.mothsAvailable = r.data.quantity;

        if(this.location == 0)
          this.locationDescription = 'at home';
        else if(this.location == 1)
          this.locationDescription = 'in your basement';
        else if(this.location == 2)
          this.locationDescription = 'on your mantle';
      }
    })
  }

  doSendMoths()
  {
    if(this.sendingMoths) return;

    this.sendingMoths = true;

    this.api.post<any>('/item/moth/release', { location: this.location, count: this.numberToSend })
      .subscribe({
        next: (r) => {
          if(this.location == 1)
            this.router.navigate([ '/basement' ]);
          else if(this.location == 2)
            this.router.navigate([ '/fireplace' ]);
          else
            this.router.navigate([ '/home' ]);
        },
        error: () => {
          this.sendingMoths = false;
        }
      })
    ;
  }

}
