import { Component, OnInit } from '@angular/core';
import {MyInventorySerializationGroup} from "../../../../model/my-inventory/my-inventory.serialization-group";
import {Subscription} from "rxjs";
import {ApiService} from "../../../shared/service/api.service";
import {ActivatedRoute, Router} from "@angular/router";
import {ApiResponseModel} from "../../../../model/api-response.model";

@Component({
    selector: 'app-philosophers-stone',
    templateUrl: './philosophers-stone.component.html',
    styleUrls: ['./philosophers-stone.component.scss'],
    standalone: false
})
export class PhilosophersStoneComponent implements OnInit {

  stoneId: number;
  dipping = false;
  inventory: MyInventorySerializationGroup[];
  inventoryAjax: Subscription;

  // should be kept in-sync with the list in PhilosophersStoneController.php
  // (or make a new endpoint to get eligible plushies... that'd be cool)
  private static readonly plushies = [
    'Bulbun Plushy',
    'Peacock Plushy',
    'Rainbow Dolphin Plushy',
    'Sneqo Plushy',
    'Phoenix Plushy',
    'Dancing Sword'
  ];

  constructor(private api: ApiService, private activatedRoute: ActivatedRoute, private router: Router) { }

  ngOnInit() {
    this.stoneId = parseInt(this.activatedRoute.snapshot.paramMap.get('id'));

    this.inventoryAjax = this.api.get<MyInventorySerializationGroup[]>('/inventory/my').subscribe({
      next: (r: ApiResponseModel<MyInventorySerializationGroup[]>) => {
        this.inventory = r.data.filter(i => PhilosophersStoneComponent.plushies.includes(i.item.name));
      }
    });
  }

  ngOnDestroy(): void {
    this.inventoryAjax.unsubscribe();
  }

  doUse(plushy: MyInventorySerializationGroup)
  {
    if(this.dipping) return;

    this.dipping = true;

    this.api.post('/item/philosophersStone/' + this.stoneId + '/use', { plushy: plushy.id }).subscribe({
      next: () => {
        this.router.navigate([ '/home' ]);
      },
      error: () => {
        this.dipping = false;
      }
    })
  }

}
