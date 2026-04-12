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
    selector: 'app-dialog-title-with-icons',
    imports: [],
    templateUrl: './dialog-title-with-icons.component.html',
    styleUrls: ['./dialog-title-with-icons.component.scss']
})
export class DialogTitleWithIconsComponent {
  icon = input.required<string>();
  iconLabel = input.required<string>();
  title = input.required<string>();
}
