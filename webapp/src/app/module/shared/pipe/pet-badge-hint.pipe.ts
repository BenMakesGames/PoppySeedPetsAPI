import { Pipe, PipeTransform } from '@angular/core';
import { petBadgeInfo } from "../pet-badge-info";

@Pipe({
  name: 'petBadgeHint',
  standalone: true
})
export class PetBadgeHintPipe implements PipeTransform {
  transform(badgeName: string, spoiler: boolean): string {
    return spoiler ? petBadgeInfo[badgeName].spoiler : petBadgeInfo[badgeName].hint;
  }
}
