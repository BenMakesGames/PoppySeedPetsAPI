import { Pipe, PipeTransform } from '@angular/core';
import {Adventure} from "../component/adventure/adventure.component";

@Pipe({
  name: 'adventureIcon',
  standalone: true
})
export class AdventureIconPipe implements PipeTransform {

  transform(value: Adventure): string {
    return 'assets/adventure-types/' + value.type.replace(/[^a-zA-Z0-9]+/, '-') + '.svg';
  }

}
