import { Component, OnInit } from '@angular/core';
import {MyInventorySerializationGroup} from "../../../../model/my-inventory/my-inventory.serialization-group";
import {Subscription} from "rxjs";
import {ApiService} from "../../../shared/service/api.service";
import {ActivatedRoute, Router} from "@angular/router";
import {ApiResponseModel} from "../../../../model/api-response.model";

@Component({
    selector: 'app-dragon-vase',
    templateUrl: './dragon-vase.component.html',
    styleUrls: ['./dragon-vase.component.scss'],
    standalone: false
})
export class DragonVaseComponent implements OnInit {

  vaseId: number;
  dipping = false;
  inventory: MyInventorySerializationGroup[];
  inventoryAjax: Subscription;

  constructor(private api: ApiService, private activatedRoute: ActivatedRoute, private router: Router) { }

  ngOnInit() {
    this.vaseId = parseInt(this.activatedRoute.snapshot.paramMap.get('id'));

    this.inventoryAjax = this.api.get<MyInventorySerializationGroup[]>('/inventory/my').subscribe({
      next: (r: ApiResponseModel<MyInventorySerializationGroup[]>) => {
        this.inventory = r.data.filter(i => !!i.item.tool);
      }
    });
  }

  ngOnDestroy(): void {
    this.inventoryAjax.unsubscribe();
  }

  doDipItem(tool: MyInventorySerializationGroup)
  {
    if(this.dipping) return;

    this.dipping = true;

    this.api.post('/item/dragonVase/' + this.vaseId + '/dip', { tool: tool.id }).subscribe({
      next: () => {
        this.router.navigate([ '/home' ]);
      },
      error: () => {
        this.dipping = false;
      }
    })
  }

}
