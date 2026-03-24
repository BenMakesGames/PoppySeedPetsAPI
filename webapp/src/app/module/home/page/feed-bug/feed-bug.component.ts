import {Component, OnDestroy, OnInit} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {MyInventorySerializationGroup} from "../../../../model/my-inventory/my-inventory.serialization-group";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {ActivatedRoute, Router} from "@angular/router";
import {Subscription} from "rxjs";

@Component({
    selector: 'app-feed-bug',
    templateUrl: './feed-bug.component.html',
    styleUrls: ['./feed-bug.component.scss'],
    standalone: false
})
export class FeedBugComponent implements OnInit, OnDestroy {

  bugId: number;
  feeding = false;
  inventory: MyInventorySerializationGroup[];
  inventoryAjax: Subscription;

  constructor(private api: ApiService, private activatedRoute: ActivatedRoute, private router: Router) { }

  ngOnInit() {
    this.bugId = parseInt(this.activatedRoute.snapshot.paramMap.get('id'));

    this.inventoryAjax = this.api.get<MyInventorySerializationGroup[]>('/inventory/my').subscribe({
      next: (r: ApiResponseModel<MyInventorySerializationGroup[]>) => {
        this.inventory = r.data.filter(i => !!i.item.food);
      }
    });
  }

  ngOnDestroy(): void {
    this.inventoryAjax.unsubscribe();
  }

  doFeedBug(food: MyInventorySerializationGroup)
  {
    if(this.feeding) return;

    this.feeding = true;

    this.api.post('/item/bug/' + this.bugId + '/feed', { food: food.id }).subscribe({
      next: () => {
        this.router.navigate([ '/home' ]);
      },
      error: () => {
        this.feeding = false;
      }
    })
  }

}
