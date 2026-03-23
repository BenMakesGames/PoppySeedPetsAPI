import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  standalone: true,
  name: 'numberWithUnits'
})
export class NumberWithUnitsPipe implements PipeTransform {

  transform(value: number, precision: number): string {
    if(value > 1000000000) return (value / 1000000000).toFixed(precision) + 'b';
    if(value > 1000000) return (value / 1000000).toFixed(precision) + 'm';
    if(value > 1000) return (value / 1000).toFixed(precision) + 'k';
    return value.toString();
  }

}
