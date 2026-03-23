import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'spiritName',
  standalone: true,
})
export class SpiritNamePipe implements PipeTransform {

  transform(type: string): string {
    switch(type)
    {
      case 'Anhur': return 'Hunter of Anhur';
      case 'Boshinogami': return 'Boshinogami';
      case 'Cardea': return 'Cardea\'s Lockbearer';
      case 'Dionysus': return 'Dionysus\' Hunger';
      case 'Huehuecoyotl': return 'Huehuecoyotl\'s Folly';
      case 'Eiri Persona': return 'Eiri Persona';
      case 'Vaf & Nir': return 'Vaf & Nir';
    }
  }

}
