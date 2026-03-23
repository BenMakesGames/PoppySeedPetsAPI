import { Component, input } from '@angular/core';

@Component({
    selector: 'app-museum-unlock-progress',
    templateUrl: './museum-unlock-progress.component.html',
    styleUrls: ['./museum-unlock-progress.component.scss'],
    standalone: false
})
export class MuseumUnlockProgressComponent {

  readonly maxDonated = 620;

  readonly milestones = [
    100, // crafting plaza box
    150, // basement blueprint, etc
    200, // Electrical Engineering Textbook
    300, // The Umbra book
    400, // more mantle space
    450, // fish bag plaza option
    600, // Book of Noods
  ];

  itemsDonated = input.required<number>();

}
