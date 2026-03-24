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
