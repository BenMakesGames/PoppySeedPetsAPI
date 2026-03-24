import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  standalone: true,
  name: 'petGroupProductsLabel'
})
export class PetGroupProductsLabelPipe implements PipeTransform {

  readonly GROUP_PRODUCT_LABELS = [
    '',
    'Albums',
    'Surveys',
    '',
    ''
  ];

  transform(value: number): any {
    if(value < 1 || value >= this.GROUP_PRODUCT_LABELS.length)
      return '<unknown type>';

    return this.GROUP_PRODUCT_LABELS[value];
  }

}
