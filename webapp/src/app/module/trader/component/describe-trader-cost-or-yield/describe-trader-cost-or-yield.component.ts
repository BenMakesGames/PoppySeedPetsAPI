/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
