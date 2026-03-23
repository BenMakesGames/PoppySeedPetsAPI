import {Component, Input} from '@angular/core';
import {TraderCostOrYieldModel} from "../../../../model/trader-cost-or-yield.model";
import {ItemDetailsDialog} from "../../../../dialog/item-details/item-details.dialog";
import { MatDialog } from "@angular/material/dialog";

@Component({
    selector: 'app-describe-yield',
    templateUrl: './describe-yield.component.html',
    styleUrls: ['./describe-yield.component.scss'],
    standalone: false
})
export class DescribeYieldComponent {

  @Input() costOrYield: TraderCostOrYieldModel;
  @Input() sizeInRem: number = 4;
  @Input() circled = true;
  @Input() compact = false;

  constructor(private matDialog: MatDialog) {
  }

  doViewItem(itemName: string)
  {
    ItemDetailsDialog.open(this.matDialog, itemName);
  }
}
