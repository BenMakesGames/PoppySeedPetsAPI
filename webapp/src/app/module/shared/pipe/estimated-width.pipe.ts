import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  standalone: true,
  name: 'estimatedWidth'
})
export class EstimatedWidthPipe implements PipeTransform {

  transform(value: string): number {
    const realLength = value.length;
    const halfWidthCount = value.replace(/[^iIltfj"'. ]/g, '').length;

    return realLength - halfWidthCount / 2;
  }

}
