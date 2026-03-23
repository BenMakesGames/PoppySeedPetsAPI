import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'theSpiritName',
  standalone: true,
})
export class TheSpiritNamePipe implements PipeTransform {

  transform(type: string): string {
    switch(type)
    {
      case 'Anhur': return 'the Hunter of Anhur';
      case 'Boshinogami': return 'the Boshinogami';
      case 'Cardea': return 'Cardea\'s Lockbearer';
      case 'Dionysus': return 'Dionysus\' Hunger';
      case 'Huehuecoyotl': return 'Huehuecoyotl\'s Folly';
      case 'Eiri Persona': return 'the Eiri Persona';
      case 'Vaf & Nir': return 'Vaf & Nir';
    }
  }
}
