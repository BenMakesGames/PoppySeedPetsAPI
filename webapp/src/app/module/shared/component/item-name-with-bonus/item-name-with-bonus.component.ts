/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import {Component, Input, OnInit} from '@angular/core';
import { CommonModule } from "@angular/common";

@Component({
    imports: [
        CommonModule
    ],
    selector: 'app-item-name-with-bonus',
    templateUrl: './item-name-with-bonus.component.html',
    styleUrls: ['./item-name-with-bonus.component.scss']
})
export class ItemNameWithBonusComponent implements OnInit {

  @Input() spice: { name: string, isSuffix: boolean };
  @Input() bonus: { name: string, isSuffix: boolean };
  @Input() item: { name: string };

  constructor() { }

  ngOnInit(): void {
  }

}
