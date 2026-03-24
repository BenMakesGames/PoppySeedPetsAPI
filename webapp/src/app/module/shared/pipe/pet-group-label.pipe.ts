import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  standalone: true,
  name: 'petGroupLabel'
})
export class PetGroupLabelPipe implements PipeTransform {

  readonly GROUP_LABELS = [
    '',
    'Band',
    'Astronomy Lab',
    'Gaming Group',
    'Sportsball Team',
  ];

  transform(value: number): any {
    if(value < 1 || value >= this.GROUP_LABELS.length)
      return '<unknown type>';

    return this.GROUP_LABELS[value];
  }

}
