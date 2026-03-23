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
