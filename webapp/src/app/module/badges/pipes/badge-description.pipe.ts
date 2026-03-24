import { Pipe, PipeTransform } from '@angular/core';
import { BADGE_INFO } from "../models/badge-info";

@Pipe({
    name: 'badgeDescription',
    standalone: false
})
export class BadgeDescriptionPipe implements PipeTransform {

  transform(value: string): string {
    if(value in BADGE_INFO)
      return BADGE_INFO[value].description;
    else
      return '???';
  }

}
