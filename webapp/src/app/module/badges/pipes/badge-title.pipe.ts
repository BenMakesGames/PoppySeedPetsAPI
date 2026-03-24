import { Pipe, PipeTransform } from '@angular/core';
import { BADGE_INFO } from "../models/badge-info";

@Pipe({
    name: 'badgeTitle',
    standalone: false
})
export class BadgeTitlePipe implements PipeTransform {

  transform(value: string): string {
    if(value in BADGE_INFO)
      return BADGE_INFO[value].title;
    else
      return '???';
  }

}
