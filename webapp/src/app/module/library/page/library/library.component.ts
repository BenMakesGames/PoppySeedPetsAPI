import { Component, OnInit } from '@angular/core';
import { Subscription } from "rxjs";
import {UserDataService} from "../../../../service/user-data.service";
import { ApiService } from "../../../shared/service/api.service";
import { MatDialog } from "@angular/material/dialog";
import { InventoryModeEnum, isSelectionMode } from "../../../../model/inventory-mode.enum";
import { LocationEnum } from "../../../../model/location.enum";
import { MyInventorySerializationGroup } from "../../../../model/my-inventory/my-inventory.serialization-group";
import { FilterResultsSerializationGroup } from "../../../../model/filter-results.serialization-group";
import { ActivatedRoute, ParamMap } from "@angular/router";
import { InventoryHelperService } from "../../../../service/inventory-helper.service";
import { ApiResponseModel } from "../../../../model/api-response.model";
import { InventoryDetailsDialog } from "../../../../dialog/inventory-details/inventory-details.dialog";
import { MyAccountSerializationGroup } from "../../../../model/my-account/my-account.serialization-group";
import { QueryStringService } from "../../../../service/query-string.service";

@Component({
    templateUrl: './library.component.html',
    styleUrls: ['./library.component.scss'],
    standalone: false
})
export class LibraryComponent implements OnInit {
  pageMeta = { title: 'Library' };

  InventoryModeEnum = InventoryModeEnum;
  LocationEnum = LocationEnum;
  inventorySelected = 0;

  waitingOnAjax = false;
  page: number = 0;

  mode: InventoryModeEnum = InventoryModeEnum.Browsing;

  user: MyAccountSerializationGroup;
  userSubscription = Subscription.EMPTY;
  pullLeverSubscription = Subscription.EMPTY;

  inventory: MyInventorySerializationGroup[] = null;
  results: FilterResultsSerializationGroup<MyInventorySerializationGroup> = null;

  inventoryAjax = Subscription.EMPTY;
  inventoryChangedSubscription = Subscription.EMPTY;

  constructor(
    private api: ApiService, private matDialog: MatDialog,
    private userData: UserDataService, private inventoryHelper: InventoryHelperService,
    private activatedRoute: ActivatedRoute
  ) {
    this.user = this.userData.user.value;
  }

  ngOnInit() {
    this.userSubscription = this.userData.user.subscribe({
      next: u => this.user = u
    });

    this.inventoryChangedSubscription = this.userData.userInventoryChanged.subscribe({
      next: () => this.doInventoryChanged(),
    });

    this.activatedRoute.queryParamMap.subscribe({
      next: (p: ParamMap) =>
      {
        const params = QueryStringService.parse(p);

        if('page' in params)
          this.page = QueryStringService.parseInt(params.page, 0);
        else
          this.page = 0;

        this.loadInventory();
      }
    });
  }

  ngOnDestroy(): void {
    this.inventoryAjax.unsubscribe();
    this.inventoryChangedSubscription.unsubscribe();
    this.userSubscription.unsubscribe();
  }

  doPullLever()
  {
    this.pullLeverSubscription = this.api.post('/library/pullLever').subscribe();
  }

  doInventoryChanged()
  {
    if(!this.results)
      return;

    this.inventoryAjax.unsubscribe();

    this.loadInventory();
  }

  private loadInventory()
  {
    this.inventorySelected = 0;

    let data: { page: number } = {
      page: this.page,
    };

    this.inventoryAjax = this.api.get<FilterResultsSerializationGroup<MyInventorySerializationGroup>>('/inventory/my/' + LocationEnum.Library, data).subscribe({
      next: r => this.processInventoryResponse(r),
      error: () => {},
    });
  }

  private processInventoryResponse(response: ApiResponseModel<FilterResultsSerializationGroup<MyInventorySerializationGroup>>)
  {
    if(response.data)
    {
      this.page = response.data.page;
      this.inventory = response.data.results;
      this.results = response.data;
    }
    else
      this.inventory = [];
  }

  doClickItem(inventory: MyInventorySerializationGroup)
  {
    if(this.waitingOnAjax || !this.inventoryAjax.closed) return;

    if(this.mode === InventoryModeEnum.Browsing)
    {
      const inventoryId = inventory.id;
      const itemDetailsDialog = InventoryDetailsDialog.open(this.matDialog, inventory);

      itemDetailsDialog.afterClosed().subscribe({
        next: () => {
          if(!itemDetailsDialog.componentInstance.sellPriceChanged)
            return;

          this.waitingOnAjax = true;

          this.inventoryHelper.findAndUpdateInventorySellPrice(this.inventory, inventoryId, itemDetailsDialog.componentInstance.newSellPrice).subscribe({
            complete: () => {
              this.waitingOnAjax = false;
            },
            error: () => {
              this.waitingOnAjax = false;
            }
          });
        }
      });

      const itemDeletedSubscription = itemDetailsDialog.componentInstance.itemDeleted.subscribe(() => {
        this.loadInventory();
      });

      itemDetailsDialog.afterClosed().subscribe(() => {
        itemDeletedSubscription.unsubscribe();
      });
    }
    else if(isSelectionMode(this.mode))
    {
      inventory.selected = !inventory.selected;

      if(inventory.selected)
        this.inventorySelected++;
      else
        this.inventorySelected--;
    }
  }
}
