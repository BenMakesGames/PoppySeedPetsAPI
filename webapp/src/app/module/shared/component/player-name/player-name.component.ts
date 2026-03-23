import { Component, input } from '@angular/core';
import { CommonModule } from "@angular/common";

@Component({
    imports: [
        CommonModule
    ],
    selector: 'app-player-name',
    templateUrl: './player-name.component.html',
    styleUrls: ['./player-name.component.scss']
})
export class PlayerNameComponent {

  player = input.required<{ id: number, name: string, icon: string }>();
  includeNumber = input<boolean>(false);

}
