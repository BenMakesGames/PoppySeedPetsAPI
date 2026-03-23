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
