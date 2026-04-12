/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, OnDestroy, OnInit} from '@angular/core';
import {Subscription} from "rxjs";
import {ApiService} from "../../../shared/service/api.service";
import {MyMarketBidsSerializationGroup} from "../../../../model/my-market-bids.serialization-group";
import {CreateBidDialog} from "../../dialog/create-bid/create-bid.dialog";
import {AreYouSureDialog} from "../../../../dialog/are-you-sure/are-you-sure.dialog";
import {UserDataService} from "../../../../service/user-data.service";
import {MyAccountSerializationGroup} from "../../../../model/my-account/my-account.serialization-group";
import { MatDialog } from "@angular/material/dialog";

@Component({
    selector: 'app-bids',
    templateUrl: './bids.component.html',
    styleUrls: ['./bids.component.scss'],
    standalone: false
})
export class BidsComponent implements OnInit, OnDestroy {

  bidsSubscription = Subscription.EMPTY;

  deleting: any = {};

  user: MyAccountSerializationGroup;
  myBids: MyMarketBidsSerializationGroup[]|null = null;
  bidCount = 0;
  openBid = 0;

  constructor(
    private api: ApiService, private matDialog: MatDialog, private userData: UserDataService
  ) { }

  ngOnInit(): void {
    this.user = this.userData.user.getValue();

    this.bidsSubscription = this.api.get<MyMarketBidsSerializationGroup[]>('/marketBid').subscribe({
      next: r => {
        this.myBids = r.data;
        this.computeBidCount();
      }
    })
  }

  private computeBidCount()
  {
    this.bidCount = this.myBids.reduce((carry, bid) => carry + bid.quantity, 0);
  }

  ngOnDestroy(): void {
    this.bidsSubscription.unsubscribe();
  }

  doCancelBid(bid: MyMarketBidsSerializationGroup)
  {
    const message = 'The bid will be canceled, and you\'ll be refunded ' + (bid.quantity * bid.bid) + '~~m~~.';

    AreYouSureDialog.open(this.matDialog, 'Really Cancel This Bid?', message).afterClosed().subscribe({
      next: confirm => {
        if(confirm)
        {
          this.deleting[bid.id] = true;

          this.api.del('/marketBid/' + bid.id).subscribe({
            next: r => {
              this.deleting[bid.id] = false;

              if(r)
              {
                this.myBids = this.myBids.filter(b => b.id !== bid.id);
                this.computeBidCount();
              }
            },
            error: () => {
              this.deleting[bid.id] = false;
            }
          });
        }
      }
    });
  }

  doCreateBid()
  {
    CreateBidDialog.open(this.matDialog).afterClosed().subscribe({
      next: d => {
        if(d)
        {
          this.myBids.unshift(d);
          this.computeBidCount();
        }
      }
    });
  }

  doToggleBid(bid: MyMarketBidsSerializationGroup)
  {
    if(this.openBid === bid.id)
      this.openBid = 0;
    else
      this.openBid = bid.id;
  }

}
