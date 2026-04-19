/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, Inject, OnInit} from '@angular/core';
import {ItemEncyclopediaSerializationGroup} from "../../../../model/encyclopedia/item-encyclopedia.serialization-group";
import {ApiService} from "../../../shared/service/api.service";
import {Subscription} from "rxjs";
import {MuseumSerializationGroup} from "../../../../model/museum.serialization-group";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";

@Component({
    selector: 'app-select-icon',
    templateUrl: './select-icon.dialog.html',
    styleUrls: ['./select-icon.dialog.scss'],
    standalone: false
})
export class SelectIconDialog implements OnInit {

  item: MuseumSerializationGroup;
  savingSubscription = Subscription.EMPTY;

  constructor(
    private dialog: MatDialogRef<SelectIconDialog>,
    @Inject(MAT_DIALOG_DATA) private data: any,
    private api: ApiService
  ) {
    this.item = data.item;
  }

  ngOnInit(): void {
  }

  doClose()
  {
    this.dialog.close();
  }

  doSelectIcon()
  {
    this.savingSubscription = this.api.post('/account/changeIcon', { item: this.item.item.id }).subscribe({
      next: () => {
        this.dialog.close();
      }
    });
  }

  public static open(matDialog: MatDialog, item: ItemEncyclopediaSerializationGroup)
  {
    return matDialog.open(SelectIconDialog, { data: { item: item }});
  }

}
