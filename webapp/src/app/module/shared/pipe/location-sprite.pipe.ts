import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  standalone: true,
  name: 'locationSprite'
})
export class LocationSpritePipe implements PipeTransform {

  readonly LOCATION_SPRITES = {
    'daycare': 'pet-shelter',
    'dragon den': 'dragon-den',
  };

  transform(location: string): string {
    if(location in this.LOCATION_SPRITES)
      return this.LOCATION_SPRITES[location];
    else
      return location;
  }

}
