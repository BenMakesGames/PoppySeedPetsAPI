import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
    name: 'fuelRating',
    standalone: false
})
export class FuelRatingPipe implements PipeTransform {

  static ratings = [
    'F', 'D', 'C', 'B', 'A', 'A+', 'S', 'S+', '★', '★★', '★★★'
  ];

  transform(value: any): any {
    if(value >= FuelRatingPipe.ratings.length)
      return 'omg';
    else
      return FuelRatingPipe.ratings[value];
  }

}
