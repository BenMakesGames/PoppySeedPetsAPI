import { Pipe, PipeTransform } from '@angular/core';
import { BADGE_INFO } from "../models/badge-info";

@Pipe({
    name: 'badgeImage',
    standalone: false
})
export class BadgeImagePipe implements PipeTransform {

  transform(value: string): string|null {
    if(value in BADGE_INFO)
      return '/assets/images/' + BADGE_INFO[value].image + '.svg';
    else
      return null;
  }

}
