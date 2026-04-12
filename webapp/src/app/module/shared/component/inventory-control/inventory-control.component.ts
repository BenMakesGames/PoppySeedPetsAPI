/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, EventEmitter, HostBinding, Input, OnDestroy, OnInit, Output } from '@angular/core';
import {MyInventorySerializationGroup} from "../../../../model/my-inventory/my-inventory.serialization-group";
import {MyPetSerializationGroup} from "../../../../model/my-pet/my-pet.serialization-group";
import {LocationEnum} from "../../../../model/location.enum";
import {ApiService} from "../../service/api.service";
import {InventoryModeEnum} from "../../../../model/inventory-mode.enum";
import {MyAccountSerializationGroup} from "../../../../model/my-account/my-account.serialization-group";
import {UserDataService} from "../../../../service/user-data.service";
import {ChooseToolAndEnchantmentDialog} from "../../../../dialog/choose-tool-and-enchantment/choose-tool-and-enchantment.dialog";
import {NavService} from "../../../../service/nav.service";
import { BulkSellDialog } from "../../dialog/bulk-sell/bulk-sell.dialog";
import { ThemeService } from "../../service/theme.service";
import { Subscription } from "rxjs";
import { MatDialog } from "@angular/material/dialog";
import { HotKeysModule } from "../../../hot-keys/hot-keys.module";
import { CommonModule } from "@angular/common";
import { LoadingThrobberComponent } from "../loading-throbber/loading-throbber.component";
import { HasUnlockedFeaturePipe } from "../../pipe/has-unlocked-feature.pipe";
import { ObserveOnScreenDirective } from "../../directive/observe-on-screen.directive";

@Component({
    selector: 'app-inventory-control',
    templateUrl: './inventory-control.component.html',
    imports: [
        HotKeysModule,
        CommonModule,
        LoadingThrobberComponent,
        HasUnlockedFeaturePipe,
        ObserveOnScreenDirective
    ],
    styleUrls: ['./inventory-control.component.scss']
})
export class InventoryControlComponent implements OnInit, OnDestroy {

  @HostBinding('class.is-sticking') isSticky = false;

  @Input() stickingSentinelTop: number|any;

  InventoryModeEnum = InventoryModeEnum;
  @Input() mode: InventoryModeEnum = InventoryModeEnum.Browsing;
  @Output() modeChange = new EventEmitter<InventoryModeEnum>();
  @Output() working = new EventEmitter<boolean>();

  waitingOnAjax = false;
  inventoryButtons = 'both';

  @Input() inventory: MyInventorySerializationGroup[];
  @Input() pets: MyPetSerializationGroup[] = null;
  @Input() allowFeeding = false;
  @Input() allowMoving = true;
  @Input() currentLocation: LocationEnum;

  @Output() inventoryRemoved = new EventEmitter<number[]>();
  @Output() petsChanged = new EventEmitter<void>();

  @Input() inventorySelected;
  @Output() inventorySelectedChange = new EventEmitter<number>();

  user: MyAccountSerializationGroup;

  inventoryButtonsSubscription = Subscription.EMPTY;

  constructor(
    private api: ApiService, private userData: UserDataService, private matDialog: MatDialog,
    private navService: NavService, private themeService: ThemeService
  ) { }

  ngOnInit() {
    this.user = this.userData.user.getValue();
    this.inventoryButtonsSubscription = this.themeService.inventoryButtons.subscribe({
      next: v => { this.inventoryButtons = v; }
    })
  }

  ngOnDestroy() {
    this.inventoryButtonsSubscription.unsubscribe();
  }

  doScrollToTop()
  {
    window.scroll(0, 0);
    (<any>document.getElementsByTagName('app-pet')[0]).focus();
  }

  doSetSticky(sticky: boolean)
  {
    this.isSticky = sticky;
    this.navService.disableHeaderShadow.next(sticky);
  }

  doCookForReal()
  {
    if(this.waitingOnAjax) return;

    const items = this.inventory.filter(i => i.selected);
    const itemIds = items.map(i => i.id);

    if(itemIds.length === 0)
      return;

    if(items.length === 2 && items.every(i => i.item.tool && i.item.enchants) && items[0].item.id !== items[1].item.id)
    {
      ChooseToolAndEnchantmentDialog.open(this.matDialog, items[0], items[1]).afterClosed().subscribe({
        next: d => {
          if(d && d.tool && d.bonus)
          {
            this.combineItems([ d.tool.id, d.bonus.id ]);
          }
        }
      })
    }
    else
    {
      this.combineItems(itemIds);
    }
  }

  private setWaitingOnAjax(waiting: boolean)
  {
    this.waitingOnAjax = waiting;
    this.working.emit(waiting);
  }

  private combineItems(itemIds: number[])
  {
    this.setWaitingOnAjax(true);

    this.api.post('/inventory/prepare', { inventory: itemIds }).subscribe({
      next: () => { this.setWaitingOnAjax(false); },
      error: () => {  this.setWaitingOnAjax(false); }
    });
  }

  doMoveForReal(moveTo: LocationEnum) {
    if (this.waitingOnAjax) return;

    const itemIds = this.inventory.filter(i => i.selected).map(i => i.id);

    if (itemIds.length === 0)
      return;

    this.setWaitingOnAjax(true);

    this.api.post('/inventory/moveTo/' + moveTo, {inventory: itemIds}).subscribe({
      next: () => {
        this.inventoryRemoved.emit(itemIds);
        this.setWaitingOnAjax(false);
      },
      error: () => {  this.setWaitingOnAjax(false); }
    });
  }

  doMoveToVault() {
    if (this.waitingOnAjax) return;

    const itemIds = this.inventory.filter(i => i.selected).map(i => i.id);

    if (itemIds.length === 0) return;

    this.setWaitingOnAjax(true);

    this.api.post('/vault/moveIn', { inventory: itemIds }).subscribe({
      next: () => {
        this.inventoryRemoved.emit(itemIds);
        this.setWaitingOnAjax(false);
      },
      error: () => { this.setWaitingOnAjax(false); }
    });
  }

  doSellForReal()
  {
    const selectedItems = this.inventory.filter(i => i.selected);

    BulkSellDialog.open(this.matDialog, selectedItems)
      .afterClosed()
      .subscribe(v => {
        if('newPrice' in v)
        {
          selectedItems.forEach(i => {
            i.sellPrice = v.newPrice;
            i.selected = false;
          });
        }
      })
    ;
  }

  doTrashForReal()
  {
    if(this.waitingOnAjax) return;

    const itemIds = this.inventory.filter(i => i.selected).map(i => i.id);

    if(itemIds.length === 0)
      return;

    this.setWaitingOnAjax(true);

    this.api.post<number[]>('/inventory/throwAway', { inventory: itemIds }).subscribe({
      next: (r) => {
        const itemsDeleted = itemIds.filter(i => !r.data.includes(i));

        this.inventoryRemoved.emit(itemsDeleted);
        this.setWaitingOnAjax(false);
      },
      error: () => {  this.setWaitingOnAjax(false); }
    });
  }

  doChangeMode(mode: InventoryModeEnum) {
    if (this.waitingOnAjax) return;

    this.setMode(mode);

    if(mode === InventoryModeEnum.Browsing)
    {
      this.inventory.forEach(i => {
        i.selected = false;
      });

      this.setInventorySelected(0);
    }
  }

  setInventorySelected(n: number)
  {
    this.inventorySelected = n;
    this.inventorySelectedChange.emit(n);
  }

  setMode(mode: InventoryModeEnum)
  {
    this.mode = mode;
    this.modeChange.emit(mode);
  }
}
