import { Component, OnInit } from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {ActivatedRoute} from "@angular/router";

@Component({
    templateUrl: './rijndael.component.html',
    styleUrls: ['./rijndael.component.scss'],
    standalone: false
})
export class RijndaelComponent implements OnInit {
  inventoryId: number;

  doingIt = false;

  results: any[];

  constructor(private api: ApiService, private activatedRoute: ActivatedRoute)
  {

  }

  ngOnInit()
  {
    this.inventoryId = parseInt(this.activatedRoute.snapshot.paramMap.get('id'));
  }

  doIt(itemId: number|null)
  {
    if(itemId === null) return;

    if(this.doingIt) return;

    this.doingIt = true;

    this.api.post<any>('/item/rijndael/' + this.inventoryId, { itemId: itemId })
      .subscribe({
        next: (r) => {
          this.results = r.data;
        },
        error: () => {
          this.doingIt = false;
        }
      })
    ;
  }

}
