import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
    name: 'describeDragonFood',
    standalone: false
})
export class DescribeDragonFoodPipe implements PipeTransform {

  transform(value: string[]): string {
    let spicy = false;
    let meaty = false;
    let fishy = false;

    value.forEach(v => {
      if(v.indexOf('spicy') >= 0) spicy = true;
      if(v.indexOf('meaty') >= 0) meaty = true;
      if(v.indexOf('fishy') >= 0) fishy = true;
    });

    let list = [];

    if(spicy) list.push('spicy');
    if(meaty) list.push('meaty');
    if(fishy) list.push('fishy');

    return list.join(', ');
  }

}
