/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
