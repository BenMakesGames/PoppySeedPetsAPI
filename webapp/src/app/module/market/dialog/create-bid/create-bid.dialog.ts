import { Component, OnInit } from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {Subscription} from "rxjs";
import {MyAccountSerializationGroup} from "../../../../model/my-account/my-account.serialization-group";
import {UserDataService} from "../../../../service/user-data.service";
import { MatDialog, MatDialogRef } from "@angular/material/dialog";

@Component({
    templateUrl: './create-bid.dialog.html',
    styleUrls: ['./create-bid.dialog.scss'],
    standalone: false
})
export class CreateBidDialog implements OnInit {

  createBidSubscription = Subscription.EMPTY;

  item: number|null = null;
  price: number;
  quantity: number;
  location = 0;
  user: MyAccountSerializationGroup;

  constructor(
    private dialogRef: MatDialogRef<CreateBidDialog>,
    private api: ApiService, private userDataService: UserDataService
  ) {

  }

  ngOnInit(): void {
    this.user = this.userDataService.user.getValue();
  }

  doCreateBid()
  {
    const data = {
      item: this.item,
      quantity: this.quantity,
      bid: this.price,
      location: this.location,
    };

    this.dialogRef.disableClose = true;

    this.createBidSubscription = this.api.post('/marketBid', data).subscribe({
      next: r => {
        if(r.success)
          this.dialogRef.close(r.data);
      },
      error: () => {
        this.dialogRef.disableClose = false;
      }
    });
  }

  doClose()
  {
    this.dialogRef.close();
  }

  public static open(matDialog: MatDialog): MatDialogRef<CreateBidDialog>
  {
    return matDialog.open(CreateBidDialog);
  }

}
