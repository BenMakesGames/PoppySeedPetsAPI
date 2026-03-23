import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
    name: 'milestoneValues',
    standalone: false
})
export class MilestoneValuesPipe implements PipeTransform {

  transform(milestones: { value: number }[]): number[] {
    return milestones.map(m => m.value);
  }

}
