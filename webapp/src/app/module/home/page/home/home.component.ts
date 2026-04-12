/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, OnDestroy, OnInit } from '@angular/core';
import { ApiService } from "../../../shared/service/api.service";
import { ApiResponseModel } from "../../../../model/api-response.model";
import { InteractTabEnum, InteractWithPetDialog } from "../../dialog/interact-with-pet/interact-with-pet.dialog";
import { DialogResponseModel } from "../../../../model/dialog-response.model";
import { Observable, Subscription } from "rxjs";
import { map } from "rxjs/operators";
import { MyPetSerializationGroup } from "../../../../model/my-pet/my-pet.serialization-group";
import { MyInventorySerializationGroup } from "../../../../model/my-inventory/my-inventory.serialization-group";
import { InventoryDetailsDialog } from "../../../../dialog/inventory-details/inventory-details.dialog";
import { UserDataService } from "../../../../service/user-data.service";
import { MyAccountSerializationGroup } from "../../../../model/my-account/my-account.serialization-group";
import { InventoryModeEnum, isSelectionMode } from "../../../../model/inventory-mode.enum";
import { LocationEnum } from "../../../../model/location.enum";
import { ThemeService } from "../../../shared/service/theme.service";
import { UserSessionService } from "../../../../service/user-session.service";
import { TutorialDialog } from "../../dialog/tutorial/tutorial.dialog";
import { InventoryHelperService } from "../../../../service/inventory-helper.service";
import { MatDialog } from "@angular/material/dialog";
import { QualityTimeDialog } from "../../dialog/quality-time/quality-time.dialog";

@Component({
    templateUrl: './home.component.html',
    styleUrls: ['./home.component.scss'],
    standalone: false
})
export class HomeComponent implements OnInit, OnDestroy {

  pageMeta = { title: 'Home' };

  LocationEnum = LocationEnum;
  InventoryModeEnum = InventoryModeEnum;

  lastClicked = [];
  longPressed;
  inventorySelected = 0;
  inventoryChangedSubscription = Subscription.EMPTY;
  petsChangedSubscription = Subscription.EMPTY;

  waitingOnAjax = false;

  mode: InventoryModeEnum = InventoryModeEnum.Browsing;
  user: MyAccountSerializationGroup;
  pets: MyPetSerializationGroup[] = null;
  inventory: MyInventorySerializationGroup[] = null;
  filteredInventory: MyInventorySerializationGroup[] = null;

  sort = 'modifiedOn';
  filter = 'all';
  isOctober = false;
  hasAnyFilters = false;
  hasAnyEdible = false;
  hasAnyCooking = false;
  hasAnyCandy = false;
  hasAnyUsable = false;
  hasAnyToolsOrBonuses = false;
  canPerformQualityTime = false;

  userSubscription = Subscription.EMPTY;
  myHouseAjax = Subscription.EMPTY;
  myPetsAjax = Subscription.EMPTY;
  inventoryAjax = Subscription.EMPTY;

  qualityTimeIcon = HomeComponent.qualityTimeIcons[0];

  static readonly qualityTimeIcons = [
    'fa-solid fa-party-horn',
    'fa-solid fa-hearts',
    'fa-solid fa-paw',
    'fa-solid fa-teddy-bear',
    'fa-solid fa-star',
  ];

  constructor(
    private api: ApiService, private matDialog: MatDialog, private userData: UserDataService,
    private themeService: ThemeService, private userSessionService: UserSessionService,
    private inventoryHelper: InventoryHelperService
  ) { }

  ngOnInit() {
    this.userSubscription = this.userData.user.subscribe({
      next: user => this.setUser(user)
    });

    this.loadHouse();
    this.sort = this.themeService.defaultHouseSort.getValue();
    this.isOctober = (new Date()).getUTCMonth() === 9;

    this.inventoryChangedSubscription = this.userData.userInventoryChanged.subscribe({
      next: d => {
        if(d)
        {
          this.inventory = d;
          this.doSortAndFilter();
        }
        else
          this.doInventoryChanged();
      }
    });

    this.petsChangedSubscription = this.userData.userPetsChanged.subscribe({
      next: d => {
        if(d)
          this.pets = d;
        else
          this.doPetsChanged();
      }
    });

    if(this.userSessionService.showTutorial)
    {
      this.userSessionService.showTutorial = false;
      TutorialDialog.show(this.matDialog);
    }
  }

  setUser(user: MyAccountSerializationGroup)
  {
    this.user = user;

    const lastPerformedQualityTimeDate = Date.parse(this.user.lastPerformedQualityTime);
    const fourHoursAgo = Date.now() - 4 * 60 * 60 * 1000;

    const couldPerformQualityTime = this.canPerformQualityTime;

    this.canPerformQualityTime = lastPerformedQualityTimeDate < fourHoursAgo;

    if(this.canPerformQualityTime && !couldPerformQualityTime)
    {
      this.qualityTimeIcon = HomeComponent.qualityTimeIcons[Math.floor(Math.random() * HomeComponent.qualityTimeIcons.length)];
    }
  }

  ngOnDestroy(): void {
    this.userSubscription.unsubscribe();
    this.inventoryChangedSubscription.unsubscribe();
    this.petsChangedSubscription.unsubscribe();
    this.myHouseAjax.unsubscribe();
    this.inventoryAjax.unsubscribe();
    this.myPetsAjax.unsubscribe();
  }

  doSortAndFilter()
  {
    this.hasAnyEdible = this.inventory.some(i => i.item.food);
    this.hasAnyCooking = this.inventory.some(i => i.item.itemGroups.some(g => g.name === 'Cooking'));
    this.hasAnyCandy = this.isOctober && this.inventory.some(i => !!(i.item.food?.candy));
    this.hasAnyToolsOrBonuses = this.inventory.some(i => i.item.tool || i.item.enchants);
    this.hasAnyUsable = this.inventory.some(i => i.item.useActions && i.item.useActions.length > 0);
    this.hasAnyFilters = this.hasAnyEdible || this.hasAnyCooking || this.hasAnyCandy || this.hasAnyToolsOrBonuses || this.hasAnyUsable;

    const allowedFilters = [ 'all' ];

    if(this.hasAnyEdible) allowedFilters.push('edible');
    if(this.hasAnyCooking) allowedFilters.push('cooking');
    if(this.hasAnyCandy) allowedFilters.push('candy');
    if(this.hasAnyToolsOrBonuses) allowedFilters.push('toolsAndBonuses');
    if(this.hasAnyUsable) allowedFilters.push('usable');

    if(allowedFilters.indexOf(this.filter) === -1)
      this.filter = 'all';

    switch(this.filter)
    {
      case 'edible':
        this.filteredInventory = this.inventory.filter(i => i.item.food);
        break;
      case 'cooking':
        this.filteredInventory = this.inventory.filter(i => i.item.itemGroups.some(g => g.name === 'Cooking'));
        break;
      case 'candy':
        this.filteredInventory = this.inventory.filter(i => !!(i.item.food?.candy));
        break;
      case 'toolsAndBonuses':
        this.filteredInventory = this.inventory.filter(i => i.item.tool || i.item.enchants);
        break;
      case 'usable':
        this.filteredInventory = this.inventory.filter(i => i.item.useActions && i.item.useActions.length > 0);
        break;
      default:
        this.filteredInventory = this.inventory;
        break;
    }

    if(this.sort === 'name') {
      this.filteredInventory = this.filteredInventory.sort((a, b) => {
        if(a.item.name < b.item.name)
          return -1;
        else if(a.item.name > b.item.name)
          return 1;
        else
          return a.id - b.id;
      });
    }
    else if(this.sort === 'modifiedOn')
    {
      this.filteredInventory = this.filteredInventory.sort((a, b) => {
        if(a.modifiedOn < b.modifiedOn)
          return 1;
        else if(a.modifiedOn > b.modifiedOn)
          return -1;
        else
          return a.id - b.id;
      });
    }
  }

  private loadPets()
  {
    this.pets = null;
    this.myPetsAjax = this.api.get<MyPetSerializationGroup[]>('/pet/my').subscribe({
      next: (r: ApiResponseModel<MyPetSerializationGroup[]>) => {
        this.pets = r.data;
      }
    });
  }

  doInventoryRemoved(itemIds: number[])
  {
    if(this.inventory && this.inventory.length > 0)
    {
      this.inventory = this.inventory.filter(i => itemIds.indexOf(i.id) === -1);

      for(let i = 0; i < this.inventory.length; i++)
        this.inventory[i].selected = false;

      this.doSortAndFilter();
    }
  }

  doInventoryChanged()
  {
    this.inventoryAjax = this.loadInventory().subscribe({
      complete: () => { this.waitingOnAjax = false; },
      error: () => { this.waitingOnAjax = false; }
    });
  }

  doPetsChanged()
  {
    this.loadPets();
  }

  private loadHouse()
  {
    this.inventory = null;
    this.pets = null;
    this.inventorySelected = 0;

    this.myHouseAjax = this.api.get<{ inventory: MyInventorySerializationGroup[], pets: MyPetSerializationGroup[] }>('/account/myHouse').subscribe({
      next: r => {
        if (r.data) {
          this.inventory = r.data.inventory;
          this.pets = r.data.pets;
          this.doSortAndFilter();
        } else {
          this.inventory = [];
          this.pets = [];
        }
      }
    });
  }

  private loadInventory(): Observable<ApiResponseModel<MyInventorySerializationGroup[]>>
  {
    this.inventory = null;
    this.inventorySelected = 0;

    return this.api.get<MyInventorySerializationGroup[]>('/inventory/my').pipe(
      map(r => {
        if(r.data)
        {
          this.inventory = r.data;
          this.doSortAndFilter();
        }
        else
          this.inventory = [];

        return r;
      })
    );
  }

  dialogAfterClosed = (r: DialogResponseModel) => {
    if(!r) return;

    if(r.inventoryChanged)
      this.loadInventory().subscribe();

    if(r.newPet)
      this.pets.push(r.newPet);

    if(r.updatedPet)
      this.pets = this.pets.map(p => p.id === r.updatedPet.id ? r.updatedPet : p);
  };

  recordLastClicked(inventory)
  {
    this.lastClicked.push(inventory);

    if(this.lastClicked.length > 2)
      this.lastClicked.shift();
  }

  doLongPress(inventory: MyInventorySerializationGroup)
  {
    this.longPressed = inventory;

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
    if(this.waitingOnAjax) return;

    if(isSelectionMode(this.mode))
    {
      this.inventoryHelper.multiSelectInventory(this.inventory, inventory.item.id, !inventory.selected, this.mode);

      this.inventorySelected = this.inventory.reduce((total, i) => total + (i.selected ? 1 : 0), 0);
    }
  }

  doClickItem(inventory: MyInventorySerializationGroup)
  {
    if(this.waitingOnAjax) return;

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
          })
        }
      });

      const itemDeletedSubscription = itemDetailsDialog.componentInstance.itemDeleted.subscribe(() => {
        this.doInventoryRemoved([ inventory.id ]);
      });

      itemDetailsDialog.afterClosed().subscribe(() => {
        itemDeletedSubscription.unsubscribe();
      })
    }
    else if(isSelectionMode(this.mode))
    {
      if(this.longPressed === inventory)
      {
        this.longPressed = null;
        return;
      }

      this.recordLastClicked(inventory);

      inventory.selected = !inventory.selected;

      if(inventory.selected)
        this.inventorySelected++;
      else
        this.inventorySelected--;
    }
  }

  doQualityTime()
  {
    QualityTimeDialog.show(this.matDialog);
  }

  doInteractWithPet(pet: MyPetSerializationGroup, event)
  {
    if(this.waitingOnAjax) return;

    if(this.mode === InventoryModeEnum.Browsing)
    {
      if(this.matDialog.openDialogs.length > 0)
        return;

      const startingTab = event.target.className.indexOf('heart-throb') >= 0 ? InteractTabEnum.AFFECTION_REWARDS : InteractTabEnum.STATUS_EFFECTS;

      let interactWithPetDialogRef = InteractWithPetDialog.open(this.matDialog, pet, this.inventory, startingTab);

      interactWithPetDialogRef
        .afterClosed()
        .subscribe((r) => {
          if(interactWithPetDialogRef.componentInstance.inventoryChanged)
            this.loadInventory().subscribe();

          this.dialogAfterClosed(r);
        })
      ;
    }
    else if(this.mode === InventoryModeEnum.Feeding)
    {
      const itemIds = this.filteredInventory.filter(i => i.selected).map(i => i.id);

      if(itemIds.length === 0)
        return;

      this.waitingOnAjax = true;

      this.api.post<MyPetSerializationGroup>('/pet/' + pet.id + '/feed', { items: itemIds }).subscribe({
        next: (r: ApiResponseModel<MyPetSerializationGroup>) => {
          this.pets = this.pets.map(p => p.id === r.data.id ? r.data : p);
          this.loadInventory().subscribe({
            next: () => { this.waitingOnAjax = false; },
            error: () => { this.waitingOnAjax = false; }
          });
        },
        error: () => {
          this.waitingOnAjax = false;
        },
      });
    }
  }
}
