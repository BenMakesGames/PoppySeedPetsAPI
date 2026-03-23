import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  standalone: true,
  name: 'inArray'
})
export class InArrayPipe implements PipeTransform {

  transform(value: any, array: any[]): any {
    return array.indexOf(value) >= 0;
  }

}
