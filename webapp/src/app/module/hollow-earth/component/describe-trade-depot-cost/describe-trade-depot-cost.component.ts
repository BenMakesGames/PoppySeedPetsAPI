import { Component, Input } from '@angular/core';
import { HollowEarthTradeCost } from "../../model/hollow-earth-trade.serialization-group";

@Component({
    selector: 'app-describe-trade-depot-cost',
    templateUrl: './describe-trade-depot-cost.component.html',
    styleUrls: ['./describe-trade-depot-cost.component.scss'],
    standalone: false
})
export class DescribeTradeDepotCostComponent {

  @Input() cost: HollowEarthTradeCost;
  @Input() quantity = 1;

  constructor() { }

}
