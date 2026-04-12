/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, Input, OnInit } from '@angular/core';
import { EmojiOrFaComponent } from "../emoji-or-fa/emoji-or-fa.component";
import { NgIf } from "@angular/common";

@Component({
    selector: 'app-pet-activity-log-tag',
    template: `<span [style.background-color]="'#' + tag.color" [style.color]="'#' + textColor"><span><app-emoji-or-fa *ngIf="tag.emoji" [icon]="tag.emoji"/></span><span class="title">{{ tag.title }}</span></span>`,
    imports: [
        EmojiOrFaComponent,
        NgIf
    ],
    styleUrls: ['./pet-activity-log-tag.component.scss']
})
export class PetActivityLogTagComponent implements OnInit {

  @Input() tag: {
    title: string;
    emoji?: string|null|undefined;
    color: string;
  };

  textColor = 'FFFFFF';

  ngOnInit(): void {
    this.textColor = this.getContrastYIQ(this.tag.color);
  }

  getContrastYIQ(hexcolor: string): string {
    hexcolor = hexcolor.replace('#', '');
    const r = parseInt(hexcolor.substring(0, 2), 16);
    const g = parseInt(hexcolor.substring(2, 4), 16);
    const b = parseInt(hexcolor.substring(4, 6), 16);
    const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
    return (yiq >= 128) ? '000000' : 'FFFFFF';
  }
}
