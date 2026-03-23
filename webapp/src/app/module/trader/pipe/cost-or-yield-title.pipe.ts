import { Pipe, PipeTransform } from '@angular/core';
import { TraderCostOrYieldModel } from "../../../model/trader-cost-or-yield.model";

@Pipe({
    name: 'costOrYieldTitle',
    standalone: false
})
export class CostOrYieldTitlePipe implements PipeTransform {

  transform(value: TraderCostOrYieldModel): string {
    if(value.type === 'money')
      return 'Money';
    else if(value.type === 'recyclingPoints')
      return 'Recycling Points';
    else if(value.type === 'item')
      return value.item.name;
    else
      return '???';
  }

}
