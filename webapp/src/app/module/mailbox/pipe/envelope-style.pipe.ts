import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'envelopeStyle',
  standalone: true
})
export class EnvelopeStylePipe implements PipeTransform {

  transform(senderName: string): unknown {
    switch(senderName)
    {
      case 'Sharuminyinka': return 'busy';
      case 'Hyssop': return 'fae';
      default: return 'beige';
    }
  }

}
