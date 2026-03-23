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
