import { Pipe, PipeTransform } from '@angular/core';
import { petBadgeInfo } from "../pet-badge-info";

@Pipe({
  name: 'petBadgeName',
  standalone: true
})
export class PetBadgeNamePipe implements PipeTransform {

  transform(badgeName: string): string {
    return PetBadgeNamePipe.getBadgeName(badgeName);
  }

  public static getBadgeName(badgeName: string): string {
    if(badgeName in petBadgeInfo)
      return petBadgeInfo[badgeName].title;

    return '???';
  }
}
