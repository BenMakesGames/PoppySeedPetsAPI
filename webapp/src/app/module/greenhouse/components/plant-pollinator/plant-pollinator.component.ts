import { Component, Input } from '@angular/core';

@Component({
    selector: 'app-plant-pollinator',
    templateUrl: './plant-pollinator.component.html',
    styleUrls: ['./plant-pollinator.component.scss'],
    standalone: false
})
export class PlantPollinatorComponent {

  @Input() pollinator: 'bees'|'butterflies';

  animationDelay = Math.random() * 12;
}
