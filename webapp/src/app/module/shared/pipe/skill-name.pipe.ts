import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  standalone: true,
  name: 'skillName'
})
export class SkillNamePipe implements PipeTransform {

  transform(value: string): string {
    if(value === 'magicBinding')
      return 'Magic-binding';
    else
      return value[0].toUpperCase() + value.substring(1).toLowerCase();
  }

}
