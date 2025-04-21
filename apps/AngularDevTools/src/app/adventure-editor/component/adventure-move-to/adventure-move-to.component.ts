import {Component, EventEmitter, Input, Output} from '@angular/core';
import {MoveTo} from "../adventure/adventure.component";

@Component({
  selector: 'app-adventure-move-to',
  templateUrl: './adventure-move-to.component.html',
  styleUrl: './adventure-move-to.component.scss',
  standalone: true,
})
export class AdventureMoveToComponent {
  @Input() adventure: MoveTo;
  @Output() adventureChange = new EventEmitter<MoveTo>();

  doUpdateAdventure()
  {
    this.adventureChange.emit(this.adventure);
  }
}
