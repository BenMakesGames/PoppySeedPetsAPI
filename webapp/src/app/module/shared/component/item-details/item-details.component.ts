/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, EventEmitter, Input, OnDestroy, OnInit, Output } from '@angular/core';
import {ItemEncyclopediaSerializationGroup} from "../../../../model/encyclopedia/item-encyclopedia.serialization-group";
import {Subscription, timer} from "rxjs";
import { CommonModule } from "@angular/common";
import { RouterLink } from "@angular/router";
import { MarkdownModule } from "ngx-markdown";
import { ItemOtherPropertiesIcons } from "../../../../model/item-other-properties-icons";
import { ItemTagsComponent } from "../item-tags/item-tags.component";

@Component({
    selector: 'app-item-details',
    templateUrl: './item-details.component.html',
    imports: [
        CommonModule,
        RouterLink,
        MarkdownModule,
        ItemTagsComponent
    ],
    styleUrls: ['./item-details.component.scss']
})
export class ItemDetailsComponent implements OnInit, OnDestroy {

  public readonly DOUBLE_QUOTE = '"';

  timerSubscription: Subscription;
  isOctober = false;

  @Input() item: ItemEncyclopediaSerializationGroup;

  @Output() clickedLink = new EventEmitter<void>();

  aSnack = Math.random() <= 0.01;
  readonly itemOtherPropertiesIcons = ItemOtherPropertiesIcons;

  constructor() { }

  ngOnInit() {
    this.timerSubscription = timer(0, 15000).subscribe({
      next: () => {
        this.isOctober = (new Date()).getUTCMonth() === 9;
      }
    })
  }

  ngOnDestroy(): void {
    this.timerSubscription.unsubscribe();
  }

  doClickLink()
  {
    this.clickedLink.emit();
  }
}
