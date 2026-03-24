import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  standalone: true,
  name: 'floor'
})
export class FloorPipe implements PipeTransform {

  transform(value: any): any {
    return Math.floor(value);
  }

}
