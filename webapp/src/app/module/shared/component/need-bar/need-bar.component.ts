/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, computed, input } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
    selector: 'app-need-bar',
    imports: [CommonModule],
    templateUrl: './need-bar.component.html',
    styleUrl: './need-bar.component.scss'
})
export class NeedBarComponent {
  label = input.required<string>();
  value = input.required<number>();

  color = computed(() => this.value() < 0 ? 'rgb(var(--color-warning))' : 'rgb(var(--color-gain))');
  leftPercent = computed(() => this.value() < 0 ? Math.max(0, 0.5 + (this.value() / 2)) : 0.5);
  widthPercent = computed(() => Math.min(0.5, Math.abs(this.value() / 2)));
}
