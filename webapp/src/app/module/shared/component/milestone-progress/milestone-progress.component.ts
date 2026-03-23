import {Component, Input, SimpleChanges} from '@angular/core';
import { CommonModule } from "@angular/common";
import { NumberWithUnitsPipe } from "../../pipe/number-with-units.pipe";

@Component({
    imports: [
        CommonModule,
        NumberWithUnitsPipe,
    ],
    selector: 'app-milestone-progress',
    templateUrl: './milestone-progress.component.html',
    styleUrls: ['./milestone-progress.component.scss']
})
export class MilestoneProgressComponent {

  @Input() currentProgress: number;
  @Input() maxProgress: number;
  @Input() milestones: number[];

  percent;

  constructor() { }

  ngOnChanges(changes: SimpleChanges): void {
    this.percent = Math.min(this.currentProgress, this.maxProgress) / this.maxProgress;
  }
}
