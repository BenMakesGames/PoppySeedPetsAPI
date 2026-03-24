import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  standalone: true,
  name: 'ceil'
})
export class CeilPipe implements PipeTransform {

  transform(value: any): any {
    return Math.ceil(value);
  }

}
