/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
