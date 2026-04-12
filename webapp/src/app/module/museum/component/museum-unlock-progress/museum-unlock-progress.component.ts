/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
