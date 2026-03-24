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
