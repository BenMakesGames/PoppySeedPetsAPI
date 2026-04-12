/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Pipe, PipeTransform } from '@angular/core';
import { CurrentMoonPhaseComponent } from "../../shared/component/current-moon-phase/current-moon-phase.component";

@Pipe({
  name: 'chanceOfChangesLove'
})
export class ChanceOfChangesLovePipe implements PipeTransform {

  transform(value: number, date: Date): number {
    let chance = value;
    const moonPhase = CurrentMoonPhaseComponent.getMoonPhase(date);

    switch(moonPhase)
    {
      case 'new':
        chance *= 1 / 2;
        break;

      case 'waxing-crescent':
      case 'waning-crescent':
        chance *= 2 / 3;
        break;

      case 'first-quarter':
      case 'last-quarter':
        chance *= 3 / 4;
        break;

      case 'waxing-gibbous':
      case 'waning-gibbous':
        chance *= 4 / 5;
        break;
    }

    return chance > 100 ? 100 : Math.ceil(chance);
  }
}
