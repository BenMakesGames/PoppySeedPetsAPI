import {Component} from '@angular/core';
import { STATUS_EFFECTS } from "../../../../model/status-effects";

@Component({
    templateUrl: './status-effects.component.html',
    styleUrls: ['./status-effects.component.scss'],
    standalone: false
})
export class StatusEffectsComponent {
  pageMeta = { title: 'Poppyopedia - Status Effects' };

  nameFilter = '';

  StatusEffects = STATUS_EFFECTS;
  StatusEffectNames = Object.keys(STATUS_EFFECTS);
}
