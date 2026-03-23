import {Component, OnDestroy, OnInit} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {MyInventorySerializationGroup} from "../../../../model/my-inventory/my-inventory.serialization-group";
import {MyFireplaceSerializationGroup} from "../../../../model/fireplace/my-fireplace.serialization-group";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {MyAccountSerializationGroup} from "../../../../model/my-account/my-account.serialization-group";
import {UserDataService} from "../../../../service/user-data.service";
import {FeedFireplaceDialog} from "../../dialog/feed-fireplace/feed-fireplace.dialog";
import {LocationEnum} from "../../../../model/location.enum";
import { InventoryModeEnum, isSelectionMode } from "../../../../model/inventory-mode.enum";
import {InventoryDetailsDialog} from "../../../../dialog/inventory-details/inventory-details.dialog";
import {FeedWhelpDialog} from "../../dialog/feed-whelp/feed-whelp.dialog";
import {Subscription} from "rxjs";
import {CustomizeStockingDialog} from "../../dialog/customize-stocking/customize-stocking.dialog";
import { ThemeService } from "../../../shared/service/theme.service";
import { InventoryHelperService } from "../../../../service/inventory-helper.service";
import { SelectPetDialog } from "../../../../dialog/select-pet/select-pet.dialog";
import { InteractWithAwayPetDialog } from "../../../pet-helpers/dialog/interact-with-away-pet/interact-with-away-pet-dialog.component";
import { MatDialog } from "@angular/material/dialog";
import { ItemOtherPropertiesIcons } from "../../../../model/item-other-properties-icons";

@Component({
    templateUrl: './fireplace.component.html',
    styleUrls: ['./fireplace.component.scss'],
    standalone: false
})
export class FireplaceComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Fireplace' };

  LocationEnum = LocationEnum;
  InventoryModeEnum = InventoryModeEnum;

  lastClicked = [];
  user: MyAccountSerializationGroup;
  loading = true;
  interactingWithFire = false;
  loadingMantle = false;
  mantle: MyInventorySerializationGroup[];
  fireplace: MyFireplaceSerializationGroup;
  whelp: WhelpModel|null = null;
  inventorySelected = 0;
  mode: InventoryModeEnum = InventoryModeEnum.Browsing;
  showStocking = false;
  showRightRail = false;

  goodIdea: string;
  naniNani: string;

  fireplaceAjax = Subscription.EMPTY;
  inventoryChangedSubscription = Subscription.EMPTY;
  lootStockingAjax = Subscription.EMPTY;

  readonly itemOtherPropertiesIcons = ItemOtherPropertiesIcons;

  constructor(
    private api: ApiService, private userData: UserDataService, private matDialog: MatDialog,
    private themeService: ThemeService, private inventoryHelper: InventoryHelperService
  ) { }

  ngOnInit() {
    this.loading = true;
    this.showStocking = (new Date()).getUTCMonth() === 11;
    this.showRightRail = this.showStocking;

    this.user = this.userData.user.getValue();

    this.loadMantle();

    // PGC
    const goodIdeas = [
      'What could possibly go wrong?',
      'This is safe, right?',
      'YOLO, I guess...',
      'This game is full of good lessons.',
      'Poppy Seed Pets: not suitable for children without adult supervision.',
    ];

    this.goodIdea = goodIdeas[Math.floor(Math.random() * goodIdeas.length)];

    const naniNanis = [
      'Oh!', 'Eh?', 'Hm?', 'What\'s this??', 'Naniiiii?!', 'Wait, what?'
    ];

    this.naniNani = naniNanis[Math.floor(Math.random() * naniNanis.length)];

    this.inventoryChangedSubscription = this.userData.userInventoryChanged.subscribe({
      next: () => {
        this.loadMantle();
      }
    })
  }

  ngOnDestroy(): void {
    this.fireplaceAjax.unsubscribe();
    this.inventoryChangedSubscription.unsubscribe();
  }

  doCustomizeStocking()
  {
    CustomizeStockingDialog.open(this.matDialog, this.fireplace.stocking).afterClosed().subscribe(
      (newStocking) => {
        if(newStocking)
        {
          this.fireplace.stocking = newStocking;
        }
      }
    );
  }

  doFeedWhelp()
  {
    FeedWhelpDialog.open(this.matDialog, this.whelp.name).afterClosed().subscribe({
      next: data => {
        if(data.hasOwnProperty('whelp'))
          this.whelp = data.whelp;
      }
    });
  }

  doLookInStocking()
  {
    if(!this.lootStockingAjax.closed) return;

    this.lootStockingAjax = this.api.post('/fireplace/lookInStocking').subscribe();
  }

  doClaimReward()
  {
    if(this.interactingWithFire) return;

    this.interactingWithFire = true;

    this.api.post<MyFireplaceSerializationGroup>('/fireplace/claimRewards').subscribe({
      next: (r: ApiResponseModel<MyFireplaceSerializationGroup>) => {
        this.fireplace = r.data;
        this.interactingWithFire = false;
      },
      error: () => {
        this.interactingWithFire = false;
      }
    })
  }

  doFeedFire()
  {
    if(this.interactingWithFire) return;

    FeedFireplaceDialog.open(this.matDialog).afterClosed().subscribe({
      next: (r) => {
        if(r)
          this.fireplace = r;
      }
    });
  }

  private loadMantle()
  {
    this.loadingMantle = true;

    this.fireplaceAjax.unsubscribe();

    this.fireplaceAjax = this.api.get<FireplaceResponse>('/fireplace').subscribe({
      next: (r: ApiResponseModel<FireplaceResponse>) => {
        this.handleFireplaceResponse(r.data);
        this.loading = false;
        this.loadingMantle = false;
      }
    });
  }

  private handleFireplaceResponse(r: FireplaceResponse)
  {
    this.mantle = r.mantle;
    this.fireplace = r.fireplace;
    this.whelp = r.whelp;

    this.showRightRail = this.showStocking || !!this.whelp || this.user.canAssignHelpers;
  }

  doInventoryRemoved(itemIds: number[])
  {
    this.mantle = this.mantle.filter(i => itemIds.indexOf(i.id) === -1);
  }

  recordLastClicked(inventory)
  {
    this.lastClicked.push(inventory);

    if(this.lastClicked.length > 2)
      this.lastClicked.shift();
  }

  doLongPress(inventory: MyInventorySerializationGroup)
  {
    if(this.themeService.multiSelectWith.getValue() !== 'longPress')
      return;

    this.multiSelect(inventory);
  }

  doDoubleClickItem(inventory: MyInventorySerializationGroup)
  {
    if(this.themeService.multiSelectWith.getValue() !== 'doubleClick')
      return;

    if(!this.lastClicked.every(c => c === inventory))
      return;

    this.multiSelect(inventory);
  }

  private multiSelect(inventory: MyInventorySerializationGroup)
  {
    if(this.interactingWithFire || this.loadingMantle) return;

    if(isSelectionMode(this.mode))
    {
      this.inventoryHelper.multiSelectInventory(this.mantle, inventory.item.id, !inventory.selected, this.mode);

      this.inventorySelected = this.mantle.reduce((total, i) => total + (i.selected ? 1 : 0), 0);
    }
  }

  doClickItem(inventory: MyInventorySerializationGroup)
  {
    if(this.interactingWithFire || this.loadingMantle) return;

    this.recordLastClicked(inventory);

    if(this.mode === InventoryModeEnum.Browsing)
    {
      const inventoryId = inventory.id;
      const itemDetailsDialog = InventoryDetailsDialog.open(this.matDialog, inventory);

      itemDetailsDialog.afterClosed().subscribe({
        next: () => {
          if(!itemDetailsDialog.componentInstance.sellPriceChanged)
            return;

          this.loadingMantle = true;

          this.inventoryHelper.findAndUpdateInventorySellPrice(this.mantle, inventoryId, itemDetailsDialog.componentInstance.newSellPrice).subscribe({
            complete: () => {
              this.loadingMantle = false;
            },
            error: () => {
              this.loadingMantle = false;
            }
          });
        }
      });

      const itemDeletedSubscription = itemDetailsDialog.componentInstance.itemDeleted.subscribe(() => {
        this.loadMantle();
      });

      itemDetailsDialog.afterClosed().subscribe(() => {
        itemDeletedSubscription.unsubscribe();
      })
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

  doAssignHelper()
  {
    SelectPetDialog.open(this.matDialog)
      .afterClosed()
      .subscribe(pet => {
        if(pet)
        {
          this.interactingWithFire = true;

          this.api.post<MyFireplaceSerializationGroup>('/fireplace/assignHelper/' + pet.id).subscribe({
            next: r => {
              this.fireplace = r.data;
              this.interactingWithFire = false;
            },
            error: () => {
              this.interactingWithFire = false;
            }
          });
        }
      })
    ;
  }

  doRecallHelper()
  {
    if(this.interactingWithFire) return;

    this.interactingWithFire = true;

    this.api.post('/pet/' + this.fireplace.helper.id + '/stopHelping').subscribe({
      next: _ => {
        this.fireplace.helper = null;
        this.interactingWithFire = false;
      },
      error: _ => {
        this.interactingWithFire = false;
      }
    });
  }

  doViewHelper()
  {
    InteractWithAwayPetDialog.open(this.matDialog, this.fireplace.helper.id, this.fireplace.helper.name, [])
      .afterClosed()
      .subscribe({
        next: v => {
          if(v && v.newPet)
          {
            this.fireplace.helper.name = v.newPet.name;
          }
        }
      })
    ;
  }
}

interface FireplaceResponse
{
  mantle: MyInventorySerializationGroup[];
  fireplace: MyFireplaceSerializationGroup;
  whelp: WhelpModel|null;
}

interface WhelpModel
{
  name: string;
  colorA: string;
  colorB: string;
  growthPercent: number;
}
