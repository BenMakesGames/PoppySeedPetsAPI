import { Pipe, PipeTransform } from '@angular/core';
import { BADGE_INFO } from "../models/badge-info";

@Pipe({
    name: 'badgeBackground',
    standalone: false
})
export class BadgeBackgroundPipe implements PipeTransform {

  transform(value: string): string {
    if(value in BADGE_INFO)
      return BADGE_INFO[value].color;
    else
      return '#ffffff';
  }

}
