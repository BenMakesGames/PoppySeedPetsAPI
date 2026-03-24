import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  standalone: true,
  name: 'petGroupProductLabel'
})
export class PetGroupProductLabelPipe implements PipeTransform {

  readonly GROUP_PRODUCT_LABELS = [
    '',
    'Album',
    'Survey',
    '',
    ''
  ];

  transform(value: number): any {
    if(value < 1 || value >= this.GROUP_PRODUCT_LABELS.length)
      return '<unknown type>';

    return this.GROUP_PRODUCT_LABELS[value];
  }

}
