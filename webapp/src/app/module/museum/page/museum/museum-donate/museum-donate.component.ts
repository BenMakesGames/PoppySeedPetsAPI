import {Component, OnDestroy, OnInit} from '@angular/core';
import {Subscription} from "rxjs";
import {FilterResultsSerializationGroup} from "../../../../../model/filter-results.serialization-group";
import {MyInventorySerializationGroup} from "../../../../../model/my-inventory/my-inventory.serialization-group";
import {ApiService} from "../../../../shared/service/api.service";
import {ApiResponseModel} from "../../../../../model/api-response.model";

@Component({
    templateUrl: './museum-donate.component.html',
    styleUrls: ['./museum-donate.component.scss'],
    standalone: false
})
export class MuseumDonateComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Museum - Donate' };

  page = 0;

  loading = true;
  donating = false;
  results: FilterResultsSerializationGroup<MyInventorySerializationGroup>;
  museumDonatablesAjax: Subscription;
  noteAboutItemBonuses: MyInventorySerializationGroup;

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

    this.museumDonatablesAjax = this.api.get<FilterResultsSerializationGroup<MyInventorySerializationGroup>>('/museum/donatable', { page: this.page }).subscribe({
      next: (r: ApiResponseModel<FilterResultsSerializationGroup<MyInventorySerializationGroup>>) => {
        this.results = r.data;
        this.loading = false;
        this.noteAboutItemBonuses = this.results.results.find(r => r.enchantment || r.spice);
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
    this.museumDonatablesAjax.unsubscribe();
  }
}
