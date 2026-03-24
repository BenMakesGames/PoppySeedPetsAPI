import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
    name: 'fertilizerRating'
})
export class FertilizerRatingPipe implements PipeTransform {

  static ratings = [
    '🤮', 'F', 'D', 'C', 'B', 'A', 'A+', 'S', 'S+', '★', '★★', '★★★'
  ];

  transform(value: any): any {
    if(value > FertilizerRatingPipe.ratings.length)
      return 'omg';
    else
      return FertilizerRatingPipe.ratings[value];
  }
}
