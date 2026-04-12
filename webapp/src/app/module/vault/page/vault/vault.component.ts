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
import { VaultStatus } from "../../model/vault-status";
import { VaultItem } from "../../model/vault-item";
import { FilterResultsSerializationGroup } from "../../../../model/filter-results.serialization-group";
import { Subscription } from "rxjs";
import { ActivatedRoute, ParamMap } from "@angular/router";
import { QueryStringService } from "../../../../service/query-string.service";
import { UserDataService } from "../../../../service/user-data.service";
import { MyAccountSerializationGroup } from "../../../../model/my-account/my-account.serialization-group";
import { MatDialog } from "@angular/material/dialog";
import { MoveOutItemDialog } from "../../dialog/move-out-item/move-out-item.dialog";

@Component({
    templateUrl: './vault.component.html',
    styleUrls: ['./vault.component.scss'],
    standalone: false
})
export class VaultComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Infinity Vault' };

  status: VaultStatus|null = null;
  contents: FilterResultsSerializationGroup<VaultItem>|null = null;
  page = 0;
  timeRemaining = '';

  user: MyAccountSerializationGroup;
  lastMoveDestination: number | null = null;

  private timerInterval: number|undefined;
  private queryParamsSubscription = Subscription.EMPTY;

  statusAjax = Subscription.EMPTY;
  contentsAjax = Subscription.EMPTY;
  openSubscription = Subscription.EMPTY;

  constructor(private api: ApiService, private activatedRoute: ActivatedRoute, private userData: UserDataService, private matDialog: MatDialog) {
    this.user = this.userData.user.getValue();
  }

  ngOnInit() {
    this.loadStatus();
  }

  ngOnDestroy() {
    if(this.timerInterval)
      clearInterval(this.timerInterval);

    this.statusAjax.unsubscribe();
    this.contentsAjax.unsubscribe();
    this.queryParamsSubscription.unsubscribe();
  }

  doOpenVault() {
    if(!this.openSubscription.closed)
      return;

    this.openSubscription = this.api.post<{ vaultOpenUntil: string }>('/vault/open').subscribe({
      next: response => {
        if(response.data)
        {
          this.loadStatus();
        }
      },
    });
  }

  private loadStatus() {
    this.statusAjax.unsubscribe();
    this.statusAjax = this.api.get<VaultStatus>('/vault/status').subscribe({
      next: response => {
        if(response.data)
        {
          this.status = response.data;

          if(this.status.isOpen)
          {
            this.startCountdown();
            this.subscribeToPageChanges();
          }
        }
      },
    });
  }

  private subscribeToPageChanges() {
    this.queryParamsSubscription.unsubscribe();
    this.queryParamsSubscription = this.activatedRoute.queryParamMap.subscribe({
      next: (p: ParamMap) => {
        const params = QueryStringService.parse(p);

        if('page' in params)
          this.page = QueryStringService.parseInt(params.page, 0);
        else
          this.page = 0;

        this.loadContents(this.page);
      }
    });
  }

  private loadContents(page: number) {
    this.contentsAjax.unsubscribe();
    this.contentsAjax = this.api.get<FilterResultsSerializationGroup<VaultItem>>('/vault/contents', { page }).subscribe({
      next: response => {
        if(response.data)
          this.contents = response.data;
      },
    });
  }

  private startCountdown() {
    this.updateTimeRemaining();

    if(this.timerInterval)
      clearInterval(this.timerInterval);

    this.timerInterval = window.setInterval(() => {
      this.updateTimeRemaining();
    }, 1000);
  }

  private updateTimeRemaining() {
    if(!this.status)
      return;

    const now = new Date().getTime();
    const until = new Date(this.status.vaultOpenUntil).getTime();
    const diff = until - now;

    if(diff <= 0)
    {
      this.timeRemaining = 'closing now';
      this.status.isOpen = false;

      if(this.timerInterval)
        clearInterval(this.timerInterval);

      this.loadStatus();
      return;
    }

    const minutes = Math.floor(diff / 60000);
    const seconds = Math.floor((diff % 60000) / 1000);

    if(minutes > 0)
      this.timeRemaining = `${minutes}m ${seconds}s`;
    else
      this.timeRemaining = `${seconds}s`;
  }

  doStartMoveOut(item: VaultItem) {
    MoveOutItemDialog.open(this.matDialog, item, this.user, this.lastMoveDestination).afterClosed().subscribe(result => {
      if(result != null) {
        this.lastMoveDestination = result;
        this.loadContents(this.page);
      }
    });
  }
}
