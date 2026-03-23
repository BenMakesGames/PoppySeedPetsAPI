import {Component, OnDestroy, OnInit} from '@angular/core';
import {ActivatedRoute} from "@angular/router";
import {Subscription} from "rxjs";
import {ItemEncyclopediaSerializationGroup} from "../../../../../model/encyclopedia/item-encyclopedia.serialization-group";
import {ApiService} from "../../../../shared/service/api.service";
import {UserDataService} from "../../../../../service/user-data.service";
import {ApiResponseModel} from "../../../../../model/api-response.model";
import {Title} from "@angular/platform-browser";

@Component({
    templateUrl: './item-encyclopedia-details.component.html',
    styleUrls: ['./item-encyclopedia-details.component.scss'],
    standalone: false
})
export class ItemEncyclopediaDetailsComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Poppyopedia - Item' };

  loading;
  itemName: string;
  item: ItemEncyclopediaSerializationGroup;

  userSubscription: Subscription;
  user;

  itemAjax: Subscription;

  constructor(
    private activatedRoute: ActivatedRoute, private api: ApiService, private userData: UserDataService,
    private titleService: Title
  ) {

  }

  ngOnInit() {
    // no need to unsubscribe from paramMap, apparently
    this.activatedRoute.paramMap.subscribe(params => {
      this.loading = true;
      this.item = null;
      this.itemName = params.get('name');

      this.itemAjax = this.api.get<ItemEncyclopediaSerializationGroup>('/encyclopedia/item/' + encodeURIComponent(this.itemName)).subscribe({
        next: (r: ApiResponseModel<ItemEncyclopediaSerializationGroup>) => {
          this.item = r.data;
          this.titleService.setTitle('Poppy Seed Pets - Poppyopedia - Item - ' + this.item.name);
          this.loading = false;
        },
        error: () => {
          this.loading = false;
        }
      });
    });

    this.userSubscription = this.userData.user.subscribe(u => { this.user = u; });
  }

  ngOnDestroy()
  {
    this.userSubscription.unsubscribe();

    if(this.itemAjax)
      this.itemAjax.unsubscribe();
  }
}
