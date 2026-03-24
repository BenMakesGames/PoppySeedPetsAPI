import {Component, Input} from '@angular/core';
import {TraderCostOrYieldModel} from "../../../../model/trader-cost-or-yield.model";
import {ItemDetailsDialog} from "../../../../dialog/item-details/item-details.dialog";
import { MatDialog } from "@angular/material/dialog";

@Component({
    selector: 'app-describe-trader-cost-or-yield',
    templateUrl: './describe-trader-cost-or-yield.component.html',
    styleUrls: ['./describe-trader-cost-or-yield.component.scss'],
    standalone: false
})
export class DescribeTraderCostOrYieldComponent {

  @Input() costOrYield: TraderCostOrYieldModel;
  @Input() multiplier = 1;
  @Input() lockedToAccount: boolean;

  constructor(private matDialog: MatDialog) {
  }

  doViewItem(itemName: string)
  {
    ItemDetailsDialog.open(this.matDialog, itemName);
  }
}
