import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'spiritGraphic',
  standalone: true,
})
export class SpiritGraphicPipe implements PipeTransform {
  transform(type: string): string {
    return type.toLowerCase().replace(/[^a-z0-9]+/g, '-');
  }
}
