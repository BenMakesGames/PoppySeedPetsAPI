/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, Input, SimpleChanges} from '@angular/core';
import { CommonModule } from "@angular/common";
import { NumberWithUnitsPipe } from "../../../shared/pipe/number-with-units.pipe";
import { ItemDetailsDialog } from "../../../../dialog/item-details/item-details.dialog";
import { MatDialog } from "@angular/material/dialog";
import { MonsterOfTheWeekModel } from "../../model/monster-of-the-week.model";

@Component({
    imports: [
        CommonModule,
        NumberWithUnitsPipe,
    ],
    selector: 'app-monster-progress',
    templateUrl: './monster-progress.component.html',
    styleUrls: ['./monster-progress.component.scss']
})
export class MonsterProgressComponent {

  @Input() monster: MonsterOfTheWeekModel;
  @Input() showPersonalContribution = true;

  maxProgress = 0;
  percent = 0;

  constructor(private matDialog: MatDialog) { }

  ngOnChanges(changes: SimpleChanges): void {
    this.maxProgress = this.monster.milestones[this.monster.milestones.length - 1].value;

    this.percent = Math.min(this.monster.communityTotal, this.maxProgress) / this.maxProgress;
  }

  doShowItemPreview(itemName: string)
  {
    ItemDetailsDialog.open(this.matDialog, itemName);
  }
}
