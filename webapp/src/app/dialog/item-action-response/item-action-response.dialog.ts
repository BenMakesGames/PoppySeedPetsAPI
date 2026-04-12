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
import {ItemActionResponseSerializationGroup} from "../../model/item-action-response.serialization-group";
import {MyInventorySerializationGroup} from "../../model/my-inventory/my-inventory.serialization-group";
import { MAT_DIALOG_DATA, MatDialog, MatDialogRef } from "@angular/material/dialog";
import { MarkdownComponent } from "ngx-markdown";
import { CommonModule } from "@angular/common";

@Component({
    templateUrl: './item-action-response.dialog.html',
    imports: [
        MarkdownComponent,
        CommonModule,
    ],
    styleUrls: ['./item-action-response.dialog.scss']
})
export class ItemActionResponseDialog implements OnInit {

  inventory: MyInventorySerializationGroup|null;
  response: ItemActionResponseSerializationGroup;
  randomTitle;

  constructor(
    @Inject(MAT_DIALOG_DATA) private data: any
  ) {
    this.inventory = data.inventory;
    this.response = data.response;

    this.randomTitle = [
      'This Happened!',
      'Guess What?',
      'Check It Out:',
      '!!?!??!',
      'FYI',
      'And Suddenly...',
      ':O',
    ][Math.floor(Math.random() * 7)];
  }

  ngOnInit() {
  }

  public static open(matDialog: MatDialog, response: ItemActionResponseSerializationGroup, inventory: MyInventorySerializationGroup|null): MatDialogRef<ItemActionResponseDialog>
  {
    return matDialog.open(ItemActionResponseDialog, {
      data: {
        response: response,
        inventory: inventory,
      }
    })
  }

}
