import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  standalone: true,
  name: 'sluggify'
})
export class SluggifyPipe implements PipeTransform {

  transform(value: string): string {
    return value
      .trim()
      .toLocaleLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
    ;
  }

}
