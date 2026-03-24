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
