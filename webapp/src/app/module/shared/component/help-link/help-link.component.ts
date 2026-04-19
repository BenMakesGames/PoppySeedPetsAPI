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
import { SvgIconComponent } from "../svg-icon/svg-icon.component";
import { RouterLink } from "@angular/router";

@Component({
    selector: 'app-help-link',
    template: `<a [routerLink]="url()"><app-svg-icon sheet="menu6" icon="meta" title="(learn more about this)"/></a>`,
    imports: [
        SvgIconComponent,
        RouterLink
    ],
    styleUrls: ['./help-link.component.scss']
})
export class HelpLinkComponent {
  link = input.required<string>();
  url = computed(() => `/poppyopedia/help/${this.link()}`);
}
