import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'petBadgeImgSrc',
  standalone: true
})
export class PetBadgeImgSrcPipe implements PipeTransform {

  transform(badgeName: string): string {
    const sanitizedName = badgeName.toLowerCase().replace(/[^a-z0-9]/, '');
    return '/assets/images/pet-badges/' + sanitizedName + '.svg';
  }

}
