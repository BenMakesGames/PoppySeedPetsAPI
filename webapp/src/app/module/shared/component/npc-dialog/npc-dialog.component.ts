/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Component, Input, OnChanges, SimpleChanges } from '@angular/core';
import { ImageComponent } from "../image/image.component";
import { CommonModule } from "@angular/common";

@Component({
    selector: 'app-npc-dialog',
    templateUrl: './npc-dialog.component.html',
    imports: [
        ImageComponent,
        CommonModule,
    ],
    styleUrls: ['./npc-dialog.component.scss']
})
export class NpcDialogComponent implements OnChanges {

  @Input() removeNegativeMargins = false;
  @Input() name: string;
  @Input() npc: string|null;
  @Input() smallerImage: boolean = false;
  @Input() showBorder: boolean = true;
  @Input() colors: {[key:string]:string};
  @Input() npcPath: string = 'npcs';

  fullPath: string;

  ngOnChanges(changes: SimpleChanges) {
    if(changes.npc || changes.npcPath)
    {
      if(this.npc)
        this.fullPath = this.npcPath + '/' + this.npc;
      else
        this.fullPath = null;
    }
  }
}
