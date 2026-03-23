import {Component, OnDestroy, OnInit} from '@angular/core';
import {Subscription} from "rxjs";
import {FilterResultsSerializationGroup} from "../../../../../model/filter-results.serialization-group";
import {MyInventorySerializationGroup} from "../../../../../model/my-inventory/my-inventory.serialization-group";
import {ApiService} from "../../../../shared/service/api.service";
import {ApiResponseModel} from "../../../../../model/api-response.model";

@Component({
    templateUrl: './museum-upgrade.component.html',
    styleUrls: ['./museum-upgrade.component.scss'],
    standalone: false
})
export class MuseumUpgradeComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Museum - Upgrade Donations' };

  page = 0;

  loading = true;
  donating = false;
  results: FilterResultsSerializationGroup<MyInventorySerializationGroup>;
  museumUpgradeablesAjax: Subscription;

  constructor(
    private api: ApiService,
  ) {

  }

  ngOnInit()
  {
    this.doSearch();
  }

  doSearch()
  {
    this.loading = true;

    this.museumUpgradeablesAjax = this.api.get<FilterResultsSerializationGroup<MyInventorySerializationGroup>>('/museum/upgradeable', { page: this.page }).subscribe({
      next: (r: ApiResponseModel<FilterResultsSerializationGroup<MyInventorySerializationGroup>>) => {
        this.results = r.data;
        this.loading = false;
      }
    })
  }

  doDonate()
  {
    const inventoryIds = this.results.results.filter(r => r.selected).map(r => r.id);

    if(inventoryIds.length === 0)
      return;

    this.loading = true;
    this.donating = true;

    this.api.post('/museum/donate', { inventory: inventoryIds }).subscribe({
      next: () => {
        this.donating = false;
        this.doSearch();
      },
      error: () => {
        this.loading = false;
        this.donating = false;
      }
    });
  }

  doClickItem(inventory: MyInventorySerializationGroup)
  {
    if(!inventory.selected)
    {
      for(let i = 0; i < this.results.results.length; i++)
      {
        if(this.results.results[i].item.id === inventory.item.id)
          this.results.results[i].selected = false;
      }
    }

    inventory.selected = !inventory.selected;
  }

  ngOnDestroy()
  {
    this.museumUpgradeablesAjax.unsubscribe();
  }
}
